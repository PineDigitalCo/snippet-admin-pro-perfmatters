#!/usr/bin/env bash
# Canonical distribution ZIP for WordPress uploads. Mirrors patterns in ../.distignore (via rsync --exclude-from).
#
# Usage: bin/build-dist-zip.sh [output-directory]
# Default output: `<plugin>/dist/`. Or: composer run dist-zip
set -euo pipefail

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
plugin_root="$(cd "${script_dir}/.." && pwd)"
plugin_slug="$(basename "${plugin_root}")"
distignore="${plugin_root}/.distignore"
out_dir="${1:-${plugin_root}/dist}"
mkdir -p "${out_dir}"

if [[ ! -f "${distignore}" ]]; then
	echo "Missing .distignore at ${distignore}" >&2
	exit 1
fi

bootstrap="${plugin_root}/${plugin_slug}.php"
if [[ ! -f "${bootstrap}" ]]; then
	bootstrap="${plugin_root}/plugin.php"
fi

if [[ ! -f "${bootstrap}" ]]; then
	# GitHub Actions checks out into the repo directory name, which may differ from the WordPress plugin slug.
	while IFS= read -r candidate; do
		if grep -q 'Plugin Name:' "${candidate}" 2>/dev/null; then
			bootstrap="${candidate}"
			plugin_slug="$(basename "${candidate}" .php)"
			break
		fi
	done < <(find "${plugin_root}" -maxdepth 1 -type f -name '*.php' ! -name 'index.php' | sort)
fi

if [[ ! -f "${bootstrap}" ]]; then
	echo "Could not find plugin bootstrap (expected ${plugin_slug}.php, plugin.php, or a top-level PHP file with a Plugin Name header)" >&2
	exit 1
fi

version_line="$(grep -E '^[[:space:]]*\*[[:space:]]*Version:' "${bootstrap}" | head -1 || true)"
version="$(echo "${version_line}" | sed -E 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*//')"
if [[ -z "${version}" ]]; then
	echo "Could not read Version from ${bootstrap}" >&2
	exit 1
fi

zip_name="${plugin_slug}-${version}.zip"
tmpdir="$(mktemp -d)"
trap 'rm -rf "${tmpdir}"' EXIT

stage="${tmpdir}/stage"
mkdir -p "${stage}/${plugin_slug}"

exclude_file="${tmpdir}/rsync-excludes"
while IFS= read -r raw || [[ -n "${raw}" ]]; do
	line="${raw%%#*}"
	line="$(echo "${line}" | sed 's/[[:space:]]*$//' | sed 's/^[[:space:]]*//')"
	[[ -z "${line}" ]] && continue
	echo "${line}"
done < "${distignore}" >"${exclude_file}"

rsync -a --delete --exclude-from="${exclude_file}" "${plugin_root}/" "${stage}/${plugin_slug}/"
(
	cd "${stage}"
	rm -f "${out_dir}/${zip_name}"
	zip -r -q "${out_dir}/${zip_name}" "${plugin_slug}"
)

echo "Built ${out_dir}/${zip_name}"
