#!/bin/bash

# Deploy GEO Files to WordPress Server
#
# This script uploads generated GEO files to your WordPress server
# using rsync over SSH.

set -e  # Exit on error

# Configuration from environment
SOURCE_DIR="${OUTPUT_DIR:-./output}"
SERVER="${DEPLOY_USER}@${DEPLOY_HOST}"
DEST_PATH="${DEPLOY_PATH}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Validate configuration
if [ -z "$DEPLOY_HOST" ] || [ -z "$DEPLOY_USER" ] || [ -z "$DEPLOY_PATH" ]; then
    echo -e "${RED}Error: Deployment configuration not set${NC}"
    echo "Please set the following environment variables:"
    echo "  - DEPLOY_HOST"
    echo "  - DEPLOY_USER"
    echo "  - DEPLOY_PATH"
    echo ""
    echo "You can set them in .env file or export them manually."
    exit 1
fi

# Check if source directory exists
if [ ! -d "$SOURCE_DIR" ]; then
    echo -e "${RED}Error: Output directory not found: $SOURCE_DIR${NC}"
    echo "Run 'composer generate' first to generate GEO files."
    exit 1
fi

# Display configuration
echo "============================================"
echo "Deploying GEO Files"
echo "============================================"
echo ""
echo "Source: $SOURCE_DIR"
echo "Server: $SERVER"
echo "Destination: $DEST_PATH"
echo ""

# Test SSH connection
echo -e "${YELLOW}Testing SSH connection...${NC}"
if ! ssh -o ConnectTimeout=10 "$SERVER" "echo 'Connection successful'" > /dev/null 2>&1; then
    echo -e "${RED}Error: Cannot connect to server${NC}"
    echo "Please check:"
    echo "  - Server is accessible"
    echo "  - SSH keys are configured"
    echo "  - Username and host are correct"
    exit 1
fi
echo -e "${GREEN}✓ SSH connection successful${NC}"
echo ""

# Confirm deployment
echo -e "${YELLOW}This will upload files to: $SERVER:$DEST_PATH${NC}"
read -p "Continue? (y/N) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Deployment cancelled."
    exit 0
fi

echo ""
echo -e "${YELLOW}Deploying files...${NC}"

# Create backup on server
echo "Creating backup..."
ssh "$SERVER" "cd $DEST_PATH && \
    if [ -f llms.txt ]; then \
        mkdir -p .geo-backup && \
        cp -f llms.txt llms-full.txt sitemap.xml docs.json ai-index.json schema.json robots.txt .geo-backup/ 2>/dev/null || true && \
        echo 'Backup created'; \
    else \
        echo 'No existing files to backup'; \
    fi"

# Upload files using rsync
echo "Uploading GEO files..."
rsync -avz --delete \
    --exclude='.git*' \
    --exclude='*.log' \
    "$SOURCE_DIR/" \
    "$SERVER:$DEST_PATH/" \
    | while read -r line; do
        echo "  $line"
    done

# Verify files
echo ""
echo -e "${YELLOW}Verifying uploaded files...${NC}"
FILES=("llms.txt" "llms-full.txt" "sitemap.xml" "docs.json" "ai-index.json" "schema.json" "robots.txt")
ALL_OK=true

for file in "${FILES[@]}"; do
    if ssh "$SERVER" "test -f $DEST_PATH/$file && echo 'exists'"; then
        echo -e "  ${GREEN}✓${NC} $file"
    else
        echo -e "  ${RED}✗${NC} $file (missing)"
        ALL_OK=false
    fi
done

echo ""
if [ "$ALL_OK" = true ]; then
    echo -e "${GREEN}============================================${NC}"
    echo -e "${GREEN}✓ Deployment successful!${NC}"
    echo -e "${GREEN}============================================${NC}"
    echo ""
    echo "You can verify by visiting:"
    echo "  - https://$(echo $DEPLOY_HOST | sed 's/^[^.]*\.//')/llms.txt"
    echo "  - https://$(echo $DEPLOY_HOST | sed 's/^[^.]*\.//')/sitemap.xml"
else
    echo -e "${RED}============================================${NC}"
    echo -e "${RED}✗ Deployment completed with errors${NC}"
    echo -e "${RED}============================================${NC}"
    exit 1
fi
