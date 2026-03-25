<?php
/**
 * Shopify Admin API Client for Geo-Friendly
 *
 * This script generates GEO files for Shopify stores using the Shopify Admin API
 *
 * Requirements:
 * - PHP 8.2 or higher
 * - cURL extension
 * - Shopify Admin API access token
 * - Shopify store domain
 *
 * Usage:
 *   php shopify-client.php --store=your-store.myshopify.com --token=your-api-token
 */

declare(strict_types=1);

namespace GeoFriendly\Shopify;

/**
 * Shopify Admin API Client
 */
class ShopifyClient
{
    private string $storeDomain;
    private string $accessToken;
    private string $apiVersion = '2024-01';
    private string $outputDir = './output';

    /**
     * Constructor
     *
     * @param string $storeDomain Shopify store domain (e.g., your-store.myshopify.com)
     * @param string $accessToken Shopify Admin API access token
     */
    public function __construct(string $storeDomain, string $accessToken)
    {
        $this->storeDomain = rtrim($storeDomain, '/');
        $this->accessToken = $accessToken;
    }

    /**
     * Set API version
     *
     * @param string $version API version (e.g., '2024-01')
     */
    public function setApiVersion(string $version): void
    {
        $this->apiVersion = $version;
    }

    /**
     * Set output directory for generated files
     *
     * @param string $dir Output directory path
     */
    public function setOutputDir(string $dir): void
    {
        $this->outputDir = $dir;

        // Create directory if it doesn't exist
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Make API request to Shopify
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array|null $data Request data
     * @return array Response data
     */
    private function makeRequest(string $endpoint, string $method = 'GET', ?array $data = null): array
    {
        $url = sprintf(
            'https://%s/admin/api/%s/%s',
            $this->storeDomain,
            $this->apiVersion,
            ltrim($endpoint, '/')
        );

        $ch = curl_init();

        $headers = [
            'X-Shopify-Access-Token: ' . $this->accessToken,
            'Content-Type: application/json',
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('cURL Error: ' . $error);
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException('API Error: HTTP ' . $httpCode . ' - ' . $response);
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON Decode Error: ' . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Get store information
     *
     * @return array Store data
     */
    public function getStoreInfo(): array
    {
        return $this->makeRequest('/shop.json')['shop'];
    }

    /**
     * Get all products
     *
     * @param int $limit Number of products to retrieve
     * @return array Products data
     */
    public function getProducts(int $limit = 250): array
    {
        $products = [];
        $params = ['limit' => $limit];
        $pageInfo = null;

        do {
            $endpoint = '/products.json';
            $query = http_build_query($params);

            if ($pageInfo) {
                $query .= '&page_info=' . urlencode($pageInfo);
            }

            $response = $this->makeRequest('/products.json?' . $query);
            $products = array_merge($products, $response['products']);

            // Check for pagination
            $linkHeader = '';
            if (isset($response['link'])) {
                $linkHeader = $response['link'];
            }

            // Extract next page info from response
            if (count($response['products']) === $limit) {
                // This is a simplified pagination - real implementation would parse Link header
                break;
            } else {
                $pageInfo = null;
            }
        } while ($pageInfo);

        return $products;
    }

    /**
     * Get all collections
     *
     * @param int $limit Number of collections to retrieve
     * @return array Collections data
     */
    public function getCollections(int $limit = 250): array
    {
        $response = $this->makeRequest('/smart_collections.json?limit=' . $limit);
        $collections = $response['smart_collections'];

        $response = $this->makeRequest('/custom_collections.json?limit=' . $limit);
        $collections = array_merge($collections, $response['custom_collections']);

        return $collections;
    }

    /**
     * Get all pages
     *
     * @param int $limit Number of pages to retrieve
     * @return array Pages data
     */
    public function getPages(int $limit = 250): array
    {
        $response = $this->makeRequest('/pages.json?limit=' . $limit);
        return $response['pages'];
    }

    /**
     * Get all blogs and articles
     *
     * @param int $limit Number of articles to retrieve
     * @return array Blogs and articles data
     */
    public function getBlogs(int $limit = 250): array
    {
        $blogsResponse = $this->makeRequest('/blogs.json?limit=' . $limit);
        $blogs = $blogsResponse['blogs'];

        $blogsWithArticles = [];

        foreach ($blogs as $blog) {
            $articlesResponse = $this->makeRequest(
                '/blogs/' . $blog['handle'] . '/articles.json?limit=' . $limit
            );

            $blogsWithArticles[] = [
                'blog' => $blog,
                'articles' => $articlesResponse['articles'],
            ];
        }

        return $blogsWithArticles;
    }

    /**
     * Generate llms.txt file
     *
     * @return string Generated llms.txt content
     */
    public function generateLlmsTxt(): string
    {
        $store = $this->getStoreInfo();
        $products = $this->getProducts(50);
        $collections = $this->getCollections(50);
        $pages = $this->getPages();
        $blogs = $this->getBlogs(10);

        $lines = [];
        $baseUrl = 'https://' . $this->storeDomain;

        // Header
        $lines[] = '# ' . $store['name'];
        $lines[] = '# ' . ($store['description'] ?: 'Online Store');
        $lines[] = '# ' . $baseUrl;
        $lines[] = '';

        // Store information
        $lines[] = 'Currency: ' . $store['currency'];
        $lines[] = 'Contact: ' . ($store['customer_email'] ?: $store['email']);
        $lines[] = '';

        // Products
        $lines[] = '# Products';
        foreach (array_slice($products, 0, 20) as $product) {
            $lines[] = $product['title'] . ': ' . $baseUrl . '/products/' . $product['handle'];
        }
        $lines[] = '';

        // Collections
        $lines[] = '# Collections';
        foreach (array_slice($collections, 0, 20) as $collection) {
            $lines[] = $collection['title'] . ': ' . $baseUrl . '/collections/' . $collection['handle'];
        }
        $lines[] = '';

        // Pages
        $lines[] = '# Content';
        foreach ($pages as $page) {
            if ($page['published_at']) {
                $lines[] = $page['title'] . ': ' . $baseUrl . '/pages/' . $page['handle'];
            }
        }
        $lines[] = '';

        // Blogs
        $lines[] = '# Blogs';
        foreach ($blogs as $blogData) {
            $blog = $blogData['blog'];
            $articles = $blogData['articles'];

            $lines[] = $blog['title'] . ': ' . $baseUrl . '/blogs/' . $blog['handle'];

            foreach (array_slice($articles, 0, 5) as $article) {
                if ($article['published_at']) {
                    $lines[] = '  ' . $article['title'] . ': ' . $baseUrl . '/blogs/' . $blog['handle'] . '/' . $article['handle'];
                }
            }
        }
        $lines[] = '';

        // Technical information
        $lines[] = '# Technical';
        $lines[] = 'Platform: Shopify';
        $lines[] = 'API Version: ' . $this->apiVersion;
        $lines[] = '';

        // Metadata
        $lines[] = '# Metadata';
        $lines[] = 'Last Updated: ' . date('Y-m-d');
        $lines[] = 'Products Count: ' . count($products);

        return implode("\n", $lines);
    }

    /**
     * Generate docs.json file
     *
     * @return string Generated docs.json content
     */
    public function generateDocsJson(): string
    {
        $store = $this->getStoreInfo();
        $products = $this->getProducts(50);
        $collections = $this->getCollections();
        $pages = $this->getPages();

        $baseUrl = 'https://' . $this->storeDomain;

        $docs = [
            'title' => $store['name'],
            'description' => $store['description'] ?: 'Online Store',
            'homepage' => $baseUrl,
            'sections' => [],
        ];

        // Add products section
        if (!empty($products)) {
            $productDocs = [];
            foreach (array_slice($products, 0, 30) as $product) {
                $productDocs[] = [
                    'title' => $product['title'],
                    'description' => strip_tags($product['body_html'] ?: ''),
                    'url' => $baseUrl . '/products/' . $product['handle'],
                    'metadata' => [
                        'type' => 'product',
                        'price' => $product['variants'][0]['price'] ?? null,
                        'vendor' => $product['vendor'],
                        'product_type' => $product['product_type'],
                    ],
                ];
            }

            $docs['sections'][] = [
                'title' => 'Products',
                'description' => 'Product catalog and information',
                'items' => $productDocs,
            ];
        }

        // Add collections section
        if (!empty($collections)) {
            $collectionDocs = [];
            foreach (array_slice($collections, 0, 20) as $collection) {
                $collectionDocs[] = [
                    'title' => $collection['title'],
                    'description' => strip_tags($collection['body_html'] ?: ''),
                    'url' => $baseUrl . '/collections/' . $collection['handle'],
                ];
            }

            $docs['sections'][] = [
                'title' => 'Collections',
                'description' => 'Product collections and categories',
                'items' => $collectionDocs,
            ];
        }

        // Add pages section
        $pageDocs = [];
        foreach ($pages as $page) {
            if ($page['published_at']) {
                $pageDocs[] = [
                    'title' => $page['title'],
                    'description' => strip_tags($page['body_html'] ?: ''),
                    'url' => $baseUrl . '/pages/' . $page['handle'],
                ];
            }
        }

        if (!empty($pageDocs)) {
            $docs['sections'][] = [
                'title' => 'Pages',
                'description' => 'Store pages and information',
                'items' => $pageDocs,
            ];
        }

        // Add metadata
        $docs['metadata'] = [
            'platform' => 'Shopify',
            'currency' => $store['currency'],
            'last_updated' => date('c'),
            'total_products' => count($products),
            'total_collections' => count($collections),
        ];

        return json_encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate sitemap.xml file
     *
     * @return string Generated sitemap.xml content
     */
    public function generateSitemap(): string
    {
        $products = $this->getProducts();
        $collections = $this->getCollections();
        $pages = $this->getPages();

        $baseUrl = 'https://' . $this->storeDomain;

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString('  ');

        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        // Homepage
        $xml->startElement('url');
        $xml->writeElement('loc', $baseUrl . '/');
        $xml->writeElement('changefreq', 'daily');
        $xml->writeElement('priority', '1.0');
        $xml->endElement();

        // Products
        foreach ($products as $product) {
            $xml->startElement('url');
            $xml->writeElement('loc', $baseUrl . '/products/' . $product['handle']);
            $xml->writeElement('lastmod', date('c', strtotime($product['updated_at'])));
            $xml->writeElement('changefreq', 'weekly');
            $xml->writeElement('priority', '0.8');
            $xml->endElement();
        }

        // Collections
        foreach ($collections as $collection) {
            $xml->startElement('url');
            $xml->writeElement('loc', $baseUrl . '/collections/' . $collection['handle']);
            $xml->writeElement('lastmod', date('c', strtotime($collection['updated_at'])));
            $xml->writeElement('changefreq', 'weekly');
            $xml->writeElement('priority', '0.7');
            $xml->endElement();
        }

        // Pages
        foreach ($pages as $page) {
            if ($page['published_at']) {
                $xml->startElement('url');
                $xml->writeElement('loc', $baseUrl . '/pages/' . $page['handle']);
                $xml->writeElement('lastmod', date('c', strtotime($page['updated_at'])));
                $xml->writeElement('changefreq', 'monthly');
                $xml->writeElement('priority', '0.6');
                $xml->endElement();
            }
        }

        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * Generate all GEO files
     *
     * @return array Generated files info
     */
    public function generateAll(): array
    {
        $generated = [];

        // Generate llms.txt
        $llmsContent = $this->generateLlmsTxt();
        $llmsPath = $this->outputDir . '/llms.txt';
        file_put_contents($llmsPath, $llmsContent);
        $generated[] = $llmsPath;

        // Generate docs.json
        $docsContent = $this->generateDocsJson();
        $docsPath = $this->outputDir . '/docs.json';
        file_put_contents($docsPath, $docsContent);
        $generated[] = $docsPath;

        // Generate sitemap.xml
        $sitemapContent = $this->generateSitemap();
        $sitemapPath = $this->outputDir . '/sitemap.xml';
        file_put_contents($sitemapPath, $sitemapContent);
        $generated[] = $sitemapPath;

        return $generated;
    }
}

// CLI Usage
if (php_sapi_name() === 'cli') {
    $options = getopt('', ['store:', 'token:', 'output::', 'version::']);

    if (!isset($options['store']) || !isset($options['token'])) {
        echo "Usage: php shopify-client.php --store=your-store.myshopify.com --token=your-api-token [--output=./output] [--version=2024-01]\n";
        echo "\nRequired:\n";
        echo "  --store     Shopify store domain (e.g., your-store.myshopify.com)\n";
        echo "  --token     Shopify Admin API access token\n";
        echo "\nOptional:\n";
        echo "  --output    Output directory (default: ./output)\n";
        echo "  --version   API version (default: 2024-01)\n";
        exit(1);
    }

    try {
        $client = new ShopifyClient($options['store'], $options['token']);

        if (isset($options['version'])) {
            $client->setApiVersion($options['version']);
        }

        if (isset($options['output'])) {
            $client->setOutputDir($options['output']);
        }

        echo "Generating GEO files for {$options['store']}...\n";

        $generated = $client->generateAll();

        echo "\nGenerated files:\n";
        foreach ($generated as $file) {
            echo "  - $file\n";
        }

        echo "\nDone!\n";

    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
