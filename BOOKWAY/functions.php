<?php

function sanitizeOutput($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function getBookCover($bookData, $default = 'uploads/books/placeholder.png') {
    // Проверяем, переданы ли данные книги или только ID
    if (is_array($bookData) && isset($bookData['photo'])) {
        $imagePath = 'uploads/books/' . $bookData['photo'];
        return file_exists($imagePath) ? $imagePath : $default;
    }
    
    return $default;
}

if (!function_exists('getStatusLabel')) {
function getStatusLabel($status) {
    $labels = [
        'planned' => 'Запланировано',
        'reading' => 'Читаю',
        'completed' => 'Прочитано',
        'dropped' => 'Брошено',
        'on_hold' => 'Отложено',
        'rereading' => 'Перечитываю'
    ];
    return $labels[$status] ?? 'Не в списке';
}
}


function getStatusOptions($currentStatus='') {
    $options = [
        '' => [
            'label' => 'Добавить в список',
            'class' => 'btn-add',
            'menu' => [
                'planned' => 'Запланировано',
                'reading' => 'Читаю',
                'completed' => 'Прочитано'
            ]
        ],
        'planned' => [
            'label' => 'Запланировано',
            'class' => 'btn-planned',
            'menu' => [
                'reading' => 'Читаю',
                'completed' => 'Прочитано',
                'dropped' => 'Брошено',
                'on_hold' => 'Отложено',
                'remove' => 'Удалить из списка'
            ]
        ],
        'reading' => [
            'label' => 'Читаю',
            'class' => 'btn-reading',
            'menu' => [
                'completed' => 'Прочитано',
                'dropped' => 'Брошено',
                'on_hold' => 'Отложено',
                'remove' => 'Удалить из списка'
            ]
        ],
        'completed' => [
            'label' => 'Прочитано',
            'class' => 'btn-completed',
            'menu' => [
                'reading' => 'Читаю',
                'dropped' => 'Брошено',
                'on_hold' => 'Отложено',
                'remove' => 'Удалить из списка'
            ]
        ],
        'dropped' => [
            'label' => 'Брошено',
            'class' => 'btn-dropped',
            'menu' => [
                'reading' => 'Читаю',
                'completed' => 'Прочитано',
                'on_hold' => 'Отложено',
                'remove' => 'Удалить из списка'
            ]
        ],
        'on_hold' => [
            'label' => 'Отложено',
            'class' => 'btn-dropped',
            'menu' => [
                'reading' => 'Читаю',
                'completed' => 'Прочитано',
                'dropped' => 'Брошено',
                'remove' => 'Удалить из списка'
            ]
        ],
        
    ];
    $nullKey = null;
    return $options[$currentStatus] ?? $options[''];
}
// тут был null вместо '' если что
?>
