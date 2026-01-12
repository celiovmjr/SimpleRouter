#!/bin/bash

# SimpleRouter Test Runner
# Run all tests with various options

set -e

echo "🧪 SimpleRouter Test Suite"
echo "=========================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if PHPUnit is installed
if [ ! -f "vendor/bin/phpunit" ]; then
    echo -e "${RED}❌ PHPUnit not found!${NC}"
    echo "Please run: composer install"
    exit 1
fi

# Default command
COMMAND="vendor/bin/phpunit"

# Parse arguments
case "${1:-all}" in
    "all")
        echo -e "${YELLOW}Running all tests...${NC}"
        $COMMAND
        ;;
    "router")
        echo -e "${YELLOW}Running Router tests...${NC}"
        $COMMAND tests/RouterTest.php
        ;;
    "validation")
        echo -e "${YELLOW}Running Validation tests...${NC}"
        $COMMAND tests/ValidationTest.php
        ;;
    "request")
        echo -e "${YELLOW}Running Request tests...${NC}"
        $COMMAND tests/RequestTest.php
        ;;
    "response")
        echo -e "${YELLOW}Running Response tests...${NC}"
        $COMMAND tests/ResponseTest.php
        ;;
    "middleware")
        echo -e "${YELLOW}Running Middleware tests...${NC}"
        $COMMAND tests/MiddlewareTest.php
        ;;
    "coverage")
        echo -e "${YELLOW}Running tests with coverage...${NC}"
        $COMMAND --coverage-html coverage
        echo -e "${GREEN}✅ Coverage report generated in coverage/index.html${NC}"
        ;;
    "verbose")
        echo -e "${YELLOW}Running all tests (verbose)...${NC}"
        $COMMAND --verbose
        ;;
    "help"|"-h"|"--help")
        echo "Usage: ./run-tests.sh [option]"
        echo ""
        echo "Options:"
        echo "  all          Run all tests (default)"
        echo "  router       Run Router tests only"
        echo "  validation   Run Validation tests only"
        echo "  request      Run Request tests only"
        echo "  response     Run Response tests only"
        echo "  middleware   Run Middleware tests only"
        echo "  coverage     Run tests with coverage report"
        echo "  verbose      Run all tests with verbose output"
        echo "  help         Show this help message"
        echo ""
        exit 0
        ;;
    *)
        echo -e "${RED}❌ Unknown option: $1${NC}"
        echo "Run './run-tests.sh help' for usage information"
        exit 1
        ;;
esac

EXIT_CODE=$?

echo ""
if [ $EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}✅ All tests passed!${NC}"
else
    echo -e "${RED}❌ Some tests failed!${NC}"
fi

exit $EXIT_CODE
