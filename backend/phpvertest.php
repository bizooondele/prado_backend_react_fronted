<?php
# Use a distinct name; don't overwrite an existing file.
test ! -e __apache_php_check.php || { echo "Check file already exists; stopping."; exit 1; }

printf '%s\n' '<?php header("Content-Type: text/plain"); echo "PHP_VERSION=", PHP_VERSION, "\nPHP_SAPI=", PHP_SAPI, "\n";' \
  | sudo tee __apache_php_check.php >/dev/null