var allTestFiles = [];
var TEST_REGEXP = /(spec|test)\.js$/i;

// Get a list of all the test files to include
Object.keys(window.__karma__.files).forEach(function (file) {
    if (TEST_REGEXP.test(file)) {
        // Normalize paths to RequireJS module names.
        // If you require sub-dependencies of test files to be loaded as-is (requiring file extension)
        // then do not normalize the paths
        var normalizedTestModule = file.replace(/^\/base\/|\.js$/g, '');
        allTestFiles.push(normalizedTestModule);
    }
});

require.config({
    // Karma serves files under /base, which is the basePath from your config file
    baseUrl: '/base',

    paths: {
        'numeral': 'lib/numeral/numeral',
        'jquery' : 'lib/jquery/dist/jquery',
        'squire' : 'lib/Squire.js/src/Squire'
    },

    // dynamically load all test files
    deps: allTestFiles,

    // Kickoff test with a little trick to make things work smoothly with Squire.js
    // @see: https://github.com/iammerrick/Squire.js/issues/31
    callback: function () {
        var alreadyRun = window.alreadyRun || false;
        if (!alreadyRun) {
            window.alreadyRun = true;
            window.__karma__.start();
        }
    }
});
