<?php

$string = "some string with some caracters";

$lexicon = [
    'some' => 'Somo',
    ' s' => ' S',
    's'    => 'S'
];

$currentStack = [
    [
        'content' => $string,
        'type' => 'string'
    ]
];

foreach ($lexicon as $odsToken => $jsToken) {

    $newStack = [];
    echo "\n" . count($currentStack) . ' elements';
    foreach ($currentStack as $index => $element) {

        if ($element['type'] === 'string') {
            echo "top";

            $stringPieces = explode($odsToken, $element['content']);

            // Reset current state
            $localStack = [];
            $count = count($stringPieces);
            foreach ($stringPieces as $j => $stringPiece) {
                // Do not a give shit about empty pieces
                if ($stringPiece !== '') {
                    $localStack[] = [
                        'content' => $stringPiece,
                        'type' => 'string'
                    ];
                }

                // If is not last element
                if ($j < ($count - 1)) {
                    $localStack[] = [
                        'content' => $jsToken,
                        'type' => 'token'
                    ];
                }
            }
            $newStack = array_merge($newStack, $localStack);

        } else {
            $newStack[] = $element;
        }

    }

    $currentStack = $newStack;

    echo "\n" . "'$jsToken' ===============================" . "\n";
    array_walk($currentStack, function ($element) {
        echo $element['content'] . ($element['type'] === 'token' ? '*' : '') . '|';
    });

}

