# Appizy

[![Build Status](https://www.travis-ci.org/Appizy/appizy.svg?branch=master)](https://www.travis-ci.org/Appizy/appizy)

Convert spreadsheet data to web content

## Setup

```bash
composer install
```

## Run

### Locally

```bash
./bin/appizy convert examples/demo-appizy.ods
```

### In a Docker container

Start by building the Docker image:

```bash
# In ./bin/docker
docker build -t appizy-cli .
```

Run Appizy in the container with:

```bash
docker run -it --rm -v "$PWD":/usr/src/myapp -w /usr/src/myapp appizy-cli ./bin/appizy convert examples/demo-appizy.ods
```

or directly using the shell script that encapsulate this command:

```bash
./bin/docker/convert.sh examples/demo-appizy.ods
```

## Test

### PHPUnit

Run unit tests inside the Docker container.

```bash
./bin/docker/phpunit.sh
```

### Javascript assets

```bash
# in ./assets/js
karma start
```
