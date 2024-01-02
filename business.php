<?php


use MongoDB\BSON\ObjectID;


function get_db()
{
    $mongo = new MongoDB\Client(
        "mongodb://localhost:27017/wai",
        [
            'username' => 'wai_web',
            'password' => 'w@i_w3b',
        ]);

    $db = $mongo->wai;

    return $db;
}

function get_zdjecia()
{
    $db = get_db();
    return $db->zdjecia->find()->toArray();
}

function get_user($login)
{
    $db = get_db();
    return $db->users->findOne(['login' => $login]);
}

function save_user($user)
{
    $db = get_db();

    $db->users->insertOne($user);
    return true;
}

function save_zdj($zdj){
    $db = get_db();

    $db->zdjecia->insertOne($zdj);
    return true;
}


function get_zdj($nazwa){
    $db = get_db();

    return $db->zdjecia->findOne(['nazwa' => $nazwa]);
}
