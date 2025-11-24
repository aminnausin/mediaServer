CURRENT_TAG=$(git describe --tags --abbrev=0 HEAD)

DEFAULT_PREVIOUS_TAG=$(git describe --tags --abbrev=0 "$CURRENT_TAG^" 2>/dev/null)

read -p "Previous Tag [$DEFAULT_PREVIOUS_TAG]: " USER_TAG

if [ "$USER_TAG" = "-" ]; then
    PREVIOUS_TAG=""
else
    PREVIOUS_TAG=${USER_TAG:-$DEFAULT_PREVIOUS_TAG}
fi

echo "Current tag: $CURRENT_TAG"
echo "Previous tag: $PREVIOUS_TAG"

if [ -z "$PREVIOUS_TAG" ]; then
    echo "No previous tag found, generating full changelog"
    COMMIT_RANGE=""
else
    COMMIT_RANGE="${PREVIOUS_TAG}..HEAD"
fi

CHANGELOG_FILE="CHANGELOG_TEMP.md"

echo "# Release $CURRENT_TAG" > "$CHANGELOG_FILE"
echo "" >> "$CHANGELOG_FILE"
echo "**Date:** $(date +%Y-%m-%d)" >> "$CHANGELOG_FILE"
echo "" >> "$CHANGELOG_FILE"
echo "## Highlights" >> "$CHANGELOG_FILE"
echo "" >> "$CHANGELOG_FILE"
echo "{ to be filled }" >> "$CHANGELOG_FILE"
echo "" >> "$CHANGELOG_FILE"
echo "## Hot Fixes" >> "$CHANGELOG_FILE"
echo "" >> "$CHANGELOG_FILE"
echo "{ to be filled }" >> "$CHANGELOG_FILE"
echo "" >> "$CHANGELOG_FILE"
echo "## What's Changed" >> "$CHANGELOG_FILE"
echo "" >> "$CHANGELOG_FILE"

if [ -z "$COMMIT_RANGE" ]; then
    COMMITS=$(git log --pretty=format:"%s	%h" --no-merges)
else
    COMMITS=$(git log --pretty=format:"%s	%h" --no-merges $COMMIT_RANGE)
fi

write_section() {
    TITLE="$1"
    PATTERN="$2"

    # matches commits to the given pattern
    MATCHES=$(printf "%s\n" "$COMMITS" | grep -E "$PATTERN" || true)

    # if there are matches, write out with scope under the title
    if [ -n "$MATCHES" ]; then
        echo "### $TITLE" >> $CHANGELOG_FILE
        echo "" >> $CHANGELOG_FILE

        printf "%s\n" "$MATCHES" | while IFS=$'\t' read -r msg hash; do
            if echo "$msg" | grep -Eq '^[a-zA-Z_]+!?\\([^)]+\\):'; then
                scope=$(echo "$msg" | sed -nE 's/^[a-zA-Z_]+!?\(([^)]+)\).*/\1/p')
                scope="**$scope:** "
            else
                scope=""
            fi

            clean_msg=$(echo "$msg" | sed -E 's/^[a-zA-Z_]+!?(\([^)]+\))?:[[:space:]]*//')

            # - scope:msg (hash)
            printf -- "- %s%s (\`%s\`)\n" "$scope" "$clean_msg" "$hash" >> "$CHANGELOG_FILE"
        done

        echo "" >> $CHANGELOG_FILE
    fi
}

# write each section
write_section "✨ Features" "^feat(\(.+\))?:"
write_section "🐛 Bug Fixes" "^fix(\(.+\))?:"
write_section "⚡ Performance" "^perf(\(.+\))?:"
write_section "🔒 Security" "^security(\(.+\))?:"
write_section "♻️ Refactoring" "^refactor(\(.+\))?:"
write_section "📝 Documentation" "^docs(\(.+\))?:"
write_section "🎨 Styling" "^style(\(.+\))?:"
write_section "🧪 Testing" "^test(\(.+\))?:"
write_section "🔧 Maintenance" "^chore(\(.+\))?:"
write_section "⚙️ CI/CD" "^ci(\(.+\))?:"
write_section "◀️ Reverts" "^revert(\(.+\))?:"

# write breaking section separately to handle commits and commit descriptions
BREAKING=$(echo "$COMMITS" | grep -E "^[a-z_]+!|BREAKING CHANGE" || true)
if [ -n "$BREAKING" ]; then
    echo "### 🚨 Breaking Changes" >> $CHANGELOG_FILE
    echo "" >> $CHANGELOG_FILE

    printf "%s\n" "$BREAKING" | while IFS=$'\t' read -r msg hash; do
        clean_msg=$(echo "$msg" | sed -E 's/^[a-z_]+!?\([^)]+\)?:\s*//')
        printf -- "- %s%s (\`%s\`)\n" "$scope" "$clean_msg" "$hash" >> "$CHANGELOG_FILE"
    done

    echo "" >> $CHANGELOG_FILE
fi

echo "---" >> $CHANGELOG_FILE
