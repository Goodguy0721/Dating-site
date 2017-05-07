<?php

return array(
    'associations' => array(
        array(
            'id'           => '1',
            'id_user'      => '0',
            'img'          => 'cat.png',
            'date_created' => '2015-03-20 11:25:09',
            'is_active'    => '1',
            'name'         => array(
                'en' => 'Does %username% remind you of this cat?',
                'ru' => 'Напоминает ли %username% этого кота?',
            ),
            'view_name' => array(
                'en' => '%username%, you look like this cat to me!',
                'ru' => '%username%, ты напоминаешь мне этого кота!',
            ),
        ),
        array(
            'id'           => '2',
            'id_user'      => '0',
            'img'          => 'kitten.png',
            'date_created' => '2015-03-20 11:25:09',
            'is_active'    => '1',
            'name'         => array(
                'en' => 'Does %username% remind you of this kitten?',
                'ru' => 'Напоминает ли %username% этого котенка?',
            ),
            'view_name' => array(
                'en' => '%username%, you look like this kitten to me!',
                'ru' => '%username%, ты напоминаешь мне этого котенка!',
            ),
        ),
        array(
            'id'           => '3',
            'id_user'      => '0',
            'img'          => 'peach.png',
            'date_created' => '2015-03-20 11:25:09',
            'is_active'    => '1',
            'name'         => array(
                'en' => 'Does %username% remind you of this peach?',
                'ru' => 'Напоминает ли %username% этот персик?',
            ),
            'view_name' => array(
                'en' => '%username%, you look like this peach to me!',
                'ru' => '%username%, ты напоминаешь мне этот персик!',
            ),
        ),
        array(
            'id'           => '4',
            'id_user'      => '0',
            'img'          => 'husky.png',
            'date_created' => '2015-03-20 11:25:09',
            'is_active'    => '1',
            'name'         => array(
                'en' => 'Does %username% remind you of this dog?',
                'ru' => 'Напоминает ли %username% этого пса?',
            ),
            'view_name' => array(
                'en' => '%username%, you look like this dog to me!',
                'ru' => '%username%, ты напоминаешь мне этого пёсика!',
            ),
        ),
        array(
            'id'           => '5',
            'id_user'      => '0',
            'img'          => 'apple.png',
            'date_created' => '2015-03-20 11:25:09',
            'is_active'    => '1',
            'name'         => array(
                'en' => 'Does %username% remind you of this apple?',
                'ru' => 'Напоминает ли %username% это яблоко?',
            ),
            'view_name' => array(
                'en' => '%username%, you look like this apple to me!',
                'ru' => '%username%, ты напоминаешь мне это яблоко!',
            ),
        ),
        array(
            'id'           => '6',
            'id_user'      => '0',
            'img'          => 'flower.png',
            'date_created' => '2015-03-20 11:25:09',
            'is_active'    => '1',
            'name'         => array(
                'en' => 'Does %username% remind you of this flower?',
                'ru' => 'Напоминает ли %username% этот цветок?',
            ),
            'view_name' => array(
                'en' => '%username%, you look like this flower to me!',
                'ru' => '%username%, ты напоминаешь мне этот цветок!',
            ),
        ),
    ),
    'associations_users' => array(
        array(
            'id'           => '1',
            'id_user'      => '7',
            'id_profile'   => '10',
            'img'          => '4_husky.png',
            'answer'       => 'awesome',
            'date_created' => '2015-03-20 11:25:09',
            'name'         => array(
                'en' => '%username%, you look like this dog to me!',
                'ru' => '%username%, ты напоминаешь мне этого песика!',
            ),
        ),
        array(
            'id'           => '2',
            'id_user'      => '7',
            'id_profile'   => '9',
            'img'          => '2_kitten.png',
            'answer'       => 'not_like',
            'date_created' => '2015-03-20 11:25:09',
            'name'         => array(
                'en' => '%username%, you look like this kitten to me!',
                'ru' => '%username%, ты напоминаешь мне этого котенка!',
            ),
        ),
        array(
            'id'           => '3',
            'id_user'      => '7',
            'id_profile'   => '8',
            'img'          => '5_apple.png',
            'answer'       => '',
            'date_created' => '2015-03-20 11:25:09',
            'name'         => array(
                'en' => '%username%, you look like this apple to me!',
                'ru' => '%username%, ты напоминаешь мне это яблоко!',
            ),
        ),
        array(
            'id'           => '4',
            'id_user'      => '7',
            'id_profile'   => '6',
            'img'          => '6_flower.png',
            'answer'       => 'awesome',
            'date_created' => '2015-03-20 11:25:09',
            'name'         => array(
                'en' => '%username%, you look like this flower to me!',
                'ru' => '%username%, ты напоминаешь мне этот цветок!',
            ),
        ),
        array(
            'id'           => '5',
            'id_user'      => '10',
            'id_profile'   => '7',
            'img'          => '6_flower.png',
            'answer'       => 'cool',
            'date_created' => '2015-03-20 11:25:09',
            'name'         => array(
                'en' => '%username%, you look like this flower to me!',
                'ru' => '%username%, ты напоминаешь мне этот цветок!',
            ),
        ),
        array(
            'id'           => '6',
            'id_user'      => '2',
            'id_profile'   => '1',
            'img'          => '4_husky.png',
            'answer'       => 'cool',
            'date_created' => '2016-07-29 11:25:09',
            'name'         => array(
                'en' => '%username%, you look like this dog to me!',
                'ru' => '%username%, ты напоминаешь мне этого пёсика!',
            ),
        ),
        array(
            'id'           => '7',
            'id_user'      => '13',
            'id_profile'   => '9',
            'img'          => '6_flower.png',
            'answer'       => 'cool',
            'date_created' => '2016-07-29 11:25:09',
            'name'         => array(
                'en' => '%username%, you look like this flower to me!',
                'ru' => '%username%, ты напоминаешь мне этот цветок!',
            ),
        ),
        array(
            'id'           => '8',
            'id_user'      => '11',
            'id_profile'   => '16',
            'img'          => '2_kitten.png',
            'answer'       => 'cool',
            'date_created' => '2016-07-29 11:25:09',
            'name'         => array(
                'en' => '%username%, you look like this kitten to me!',
                'ru' => '%username%, ты напоминаешь мне этого котенка!',
            ),
        ),
         array(
            'id'           => '9',
            'id_user'      => '4',
            'id_profile'   => '16',
            'img'          => '1_cat.png',
            'answer'       => 'cool',
            'date_created' => '2016-07-29 11:25:09',
            'name'         => array(
                'en' => '%username%, you look like this cat to me!',
                'ru' => '%username%, ты напоминаешь мне этого кота!',
            ),
        ),
    ),
);
