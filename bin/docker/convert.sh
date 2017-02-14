#!/usr/bin/env bash
docker run -it --rm -e XDEBUG_CONFIG="remote_enable=1 remote_mode=req remote_port=9000 remote_host=172.17.42.1 remote_connect_back=0" -e PHP_IDE_CONFIG="serverName=docker" -v "$PWD":/usr/src/myapp -w /usr/src/myapp appizy-cli ./bin/appizy convert $@
