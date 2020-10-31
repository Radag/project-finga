<?php
namespace App;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of const
 *
 * @author adam
 */
class Constant  {
    //zpusoby zaplacení
    //převodem na účet
    const PAY_TRANSFER = 1;
    //dobírka
    const PAY_COD = 2;
    const PAY_PAYPAL_EU = 3;
    const PAY_PAYPAL_USD = 4;
    
    const PAY_COD_SK = 5;
    
    //stavy objednávky
    //zrušená objednávka
    const ORDER_CANCELED = 0;
    //uloženo zboží
    const ORDER_SAVE = 1;
    //potvrzena dodací adresa
    const ORDER_CONFIRM = 2;
    //objednávka správně vytvořena
    const ORDER_CREATED = 3;
    //objednávka byla úspěšně vyřízena
    const ORDER_DONE = 4;
    
    
    //Stavy u produktu
    //vymazaný
    const PRODUCT_DELETED = 0;
    //aktivní
    const PRODUCT_ACTIVE = 1;
    //neaktivní
    const PRODUCT_INACTIVE = 2;
    //jednotlivé texty u tabulky FINGA_TEXTS
    const TEXT_ONAS = 1;
    const TEXT_ABOUT = 2;
    const TEXT_TYM = 3;
    const TEXT_TEAM = 4;
    const TEXT_OBCHODNI_PODMINKY = 5;
    const TEXT_TERMS_OF_TRADE = 6;
    const TEXT_DISTRIBUTION = 7;
    const TEXT_DISTRIBUCE = 8;
    const TEXT_DOPRAVA = 9;
    const TEXT_SHIPPING = 10;
    
    //status uživatele
    const USER_ORDINARY = 0;
    const USER_ADMIN = 1;
    const USER_DELETED = 2;
    
    
    //články
    const ARTICLE_ACTIVE = 0;
    const ARTICLE_DELETE = 1;
    
    //videa
    const VIDEO_ACTIVE = 0;
    const VIDEO_MAINPAGE = 1;
    const VIDEO_DELETED = 2;
    
    //kategorie v obchode
    const CATEGORY_ACTIVE = 0;
    const CATEGORY_DELETED = 1;
    
    //typy obrázků
    
    const IMAGE_PRODUCT = 0;
    const IMAGE_PRODUCT_PREVIEW = 8;
    const IMAGE_POSTER_CZ_PREVIEW = 1;
    const IMAGE_POSTER_CZ = 2;
    const IMAGE_RIGHT_IMAGE = 3;  
    const IMAGE_RIGHT_IMAGE_PREVIEW = 7;    
    const IMAGE_PRODUCT_GALLERY = 4;
    const IMAGE_PRODUCT_GALLERY_PREVIEW = 5;
    const IMAGE_DISTRIBUTION = 6;
    const IMAGE_ARTICLE = 9;
    const IMAGE_FRONT_CZ_IMAGE = 10;  
    const IMAGE_FRONT_EN_IMAGE = 11;
    const IMAGE_POSTER_EN_PREVIEW = 12;
    const IMAGE_POSTER_EN = 13;
    //stavy distribuce
    
    const DISTRIBUTION_ACTIVE = 1;
    const DiSTRIBUTION_DELETED = 2;
    
     //stavy odkazu
    
    const LINK_ACTIVE = 0;
    const LINK_DELETED = 1;
    
    //verze javascriptu
    const JS_VERSION = 10;
    
    //nastavení kde se server nachází
    const NOREPLY_EMAIL = 'noreply@m.fingafingerboards.com';
    const SERVER_ADRESS = 'http://www.fingafingerboards.com';
}
?>
