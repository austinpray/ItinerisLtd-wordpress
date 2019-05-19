#!/usr/bin/env bash

# Exit script if you try to use an uninitialized variable.
set -o nounset
# Exit script if a statement returns a non-true return value.
set -o errexit
# Use the error status of the first failure, rather than that of the last item in a pipeline.
set -o pipefail

site_source1="$1"
site_source2="$2"

remote=$(git config remote.origin.url)
described_rev=$(git rev-parse HEAD | git name-rev --stdin)

cd $(mktemp -d)
git init
git remote add --fetch origin "$remote"

# switch into the gh-pages branch
if git rev-parse --verify origin/gh-pages > /dev/null 2>&1
then
    git checkout gh-pages
    # delete any old site as we are going to replace it
    # Note: this explodes if there aren't any, so moving it here for now
    git rm -rf .
else
    git checkout --orphan gh-pages
fi

cp -av $site_source1 $site_source2 .

git add -A
git status
git commit -m "Built at $(date -u +'%Y-%m-%dT%H:%M:%SZ') #$described_rev [ci skip]"
git push origin gh-pages

echo "Deployed successfully"
