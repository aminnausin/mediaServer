param(
    [string]$Tag = "test",
    [switch]$Push
)

$commit = git rev-parse --short HEAD
$version = git describe --tags --abbrev=0 2>$null
if (!$version) { $version = "unknown" }

Write-Output $commit
Write-Output $version

docker build . -t "aminnausin/mediaserver:$Tag" --build-arg GIT_COMMIT=$commit --build-arg GIT_TAG=$version

if ($Push) {
    docker push "aminnausin/mediaserver:$Tag"
}
