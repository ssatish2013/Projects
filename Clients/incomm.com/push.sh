BRANCH=$(git branch 2> /dev/null | sed -e '/^[^*]/d' -e 's/* \(.*\)/\1/')
echo "Checking out release..."
git checkout release
echo "Pulling upstream..."
git pull upstream release
echo "Checking out $BRANCH..."
git checkout $BRANCH
echo "Rebasing off of release..."
git rebase release
echo "Pushing $BRANCH to origin..."
git push origin $BRANCH
echo "Done!"
