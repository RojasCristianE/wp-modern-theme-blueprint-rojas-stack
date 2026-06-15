#!/bin/bash
# ddev-generated

# Install bun if it's not already installed
if ! command -v bun &> /dev/null; then
    curl -fsSL https://bun.sh/install | bash
    # Make bun available in the current path for the rest of the hooks
    export PATH="$PATH:$HOME/.bun/bin"
fi
