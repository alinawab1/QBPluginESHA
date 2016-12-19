<?php

function post_install() {
    global $db;
    $query = "ALTER TABLE product_templates ADD qb_product_name VARCHAR(31);";
    $db->query($query);
    $query = "update product_templates set qb_product_name  = IF (CHAR_LENGTH(name)>31,CONCAT(SUBSTRING(name, 1, 15),'...',SUBSTRING(name, -5)), name )";
    $db->query($query);
}
