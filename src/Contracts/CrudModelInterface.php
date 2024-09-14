<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 12.
 * Time: 10:21
 */

namespace DelocalZrt\SimpleCrud\Contracts;


interface CrudModelInterface
{

    /*
    pl.
    return [
        'id' => 'ID', => mindig az első helyen és így.
        'mező_neve_VAGY_kapcsolatot_megvalósiító_fg_neve|többi-option-a-sorrend-mindegy|...|" => "Mező megjelenítendő neve"
        'name|text|all|required' => 'Név',
        'email|email|all|required' => 'Email',
        'cv_file|file|show|create|update|460x460|app/cvs/|keepfilename' => 'Önéletrajz fájl',
        'userTerm.version|select.UserTerm|show|create|update|required' => 'ÁSZF verzió',
        'attrJobCategories.name|multiselect.AttrJobCategory|show|create|update' => 'Kategória',
        'languages|custom|index|show|create|update' => 'Nyelvek',

    ]
    ==============================================================
    Option-ök:
    ===============================================================
    Típus:
    custom, hidden, text, password, email, number, textarea, json, texteditor, checkbox, datetime-local, date, time, select, multiselect, file, image, multifile, multiimage
    --------------------
    Megjelenés:
    all, index, show, update, create
    ----------------------------------
    Kötelező mező?
    required
    -------------------------------------
    Többnyelvűséget engedjük? (csak text, textarea,texteditor mezőknél)
    multilang
    ---------------------------------------
    File, image-hez további opciók:
    app/cvs/ => (megadhatjuk, hogy a storage mappán belül, hova mentse a fájlt, legalább egy "/" jelnek kell benne lennie praktikusan a végén)
    640x480 => (image típus esetbében megadhatjuk, hogy mekkorára méretezze a képet mentés előtt)
    thumb100x100 => thumb méretét lehet meghatározni (ugyanoda menti thumb_FÁJLNEVE néven a thum képeket)
    keepfilename => (maradjon-e a fájl neve, alapértelmezetten új egyedi fájlnevet generál)

    multifile és multiimage mezőnél csak egyenként lehet feltölteni a fájlokat! és maga a mező json, ahol tömbben a fájlok lokációit menti
    ====================================================================

    Egyéb:
    - select, multiselect egy-a-többhöz és több-a-többhöz adatbázis kapcsolatokat jelentenek, pl select.attrSchoolDegree
    (attrSchoolDegree() => belongsTo-t megvalósító method neve) ehhez kelleni fog még select és multiselect feltöltése:
    select.Modelneve (pl. select.AttrSchoolDegree (ilyenkor megnézi, hogy van-e name vagy title mező, ha nincs, akkor az ID-t
    írja ki az select option-ökbe) lsd readme.
    - custom esetében nem fog FORM elemet készíteni a create-nél és update-nál, listenerrel kell megcsinálnunk, ha kell. lsd readme.

     * @return array
     */
    public static function getAttributesInfo(): array;

}
