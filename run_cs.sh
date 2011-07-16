#!/bin/sh

# Run PHP Code sniffer
phpcs --report=full --report-file=phpcs.out ./app/ ./jobs/

