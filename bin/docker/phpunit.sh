#!/usr/bin/env bash
docker run -it --rm -v "$PWD":/usr/src/myapp -w /usr/src/myapp appizy/appizy-cli vendor/bin/phpunit
