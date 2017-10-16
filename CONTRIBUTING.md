## How to contribute?

Foreword: please use the Docker environement to develop any patch or feature for Appizy.

Appizy contains a large chunk of legacy code... do not hesitate to ask any question about the functionality of the code.

### Propose a PR

Some advices before proposing a PR:

- Extend the testing suite as you propose a bugfix or a new feature,
- Automated tests: run the test suite ```./bin/docker/phpunit.sh```,
- Visual check: convert the fixture file ```./tests/fixtures/demo-appizy.ods```.


### Repository organisation

#### Global approach

Appizy is a OpenDocument Spreadsheet parser and renderer library. Parsing is done using PHP and rendering using Twig.

Appizy is developed with PHP 5.6. All unit tests and integration tests can be run using Docker. Image available at [https://github.com/Appizy/docker-cli](https://github.com/Appizy/docker-cli)

Once parsed, the content of the spreadsheet can be rendered using theme. The default and mainly used theme is _webapp_ reproducing the spreadsheet calculations and formating in one HTML file.

#### Repository content

- assets: JavaScript assets for the theme _webapp_. This could be moved into the ./theme/webapp folder to make it more clear.
- bin: command lines to launch conversion or unit tests
- dist: put the spreadsheet you want to convert here. This makes it easy for the docker image to grab the file and convert it. Plus the gitignore is setup to ignore everything inside
- example: a spreadsheet example to demonstrate Appizy capabilities
- src: the PHP parser
- tests: integration and unit tests for the parser
- theme: all available themes to render the spreadsheet
