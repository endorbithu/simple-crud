# SIMPLE CRUD

Testreszabható CRUD modul Laravelhez. Az config('simplecrud.namespaces') -namespacekben lévő \Models\ -ben definiált eloquent modelekhez lehet hozzárendelni. Mivel
ajax-os adatmozgás van mindenhol, így nagy mérteű adatbázis táblákkal is használható.

Előfeltétel a `delocalzrt\datatable` modul.  
A hatókörben lévő modellek elérése: `URL/simplecrud/{ModelNeve}`

## Telepítés

### Telepítés git submodulként

Ha submodulként telepítettük, akkor a composer.json-ben fel kell tüntetni path-ként a mappát ahova clone-oztuk a repot.

``` 
 "require": {
        (...)
        "delocalzrt/simple-crud": "*"
    },
"repositories": [
        {
            "type":"path",
            "url": "./app_delocalzrt/simple-crud"
        }
    ]
```

aztán `composer update`

### Telepítés composerrel

https://app.repman.io/ (belépés szükséges)

- delocalzrt organization-nél kell másolni a composer config parancsot
- és a project laravel root mappájában futtatni:

```
composer config --global --auth http-basic.delocalzrt.repo.repman.io token 2640e..........
```

Laravel root `composer.json`-nál ha még nincs, akkor be kell állítani a `delocalzrt` repot

```
"repositories": [
  {
    "type": "composer",
    "url": "https://delocalzrt.repo.repman.io"
  }
]
```

Aztán a laravel rootban:

```
composer require delocalzrt/simple-crud
```

Mivel a package composer.json -jában szerepel a

```
"extra": {
    "laravel": {
      "providers": [
        "DelocalZrt\\SimpleCrud\\Providers\\SimpleCrudServiceProvider"
      ]
    }
  }
```

rész, így a laravel meg fogja találni megadott provider-t, tehát regisztrálódnak az ott megadott dolgok.

### Publikálás

Publikáljuk a packaget = alkalmazza a providert és tartalmát:

- config fájlt másolja a `{laravelroot}/config` -ba
- `App\SimpleCrud` mappát létrehozza

```
php artisan vendor:publish
 Which provider or tag's files would you like to publish?:
  [0 ] Publish files from all providers and tags listed below
  [1 ] Provider: DelocalZrt\SimpleCrud\Providers\SimpleCrudServiceProvider
```

## Működés

### Megjelenés

A `config/simplecrud.php` -ban széleskörűen tudjuk állítani a :

- megjelenést: `class` -okat tudunk megadni a SimpleCrud oldalak html elemeihez `config('simplecrud.html_class....')`
- tartalmat: meg lehet adni, hogy a Simplecrud oldalaknál milyen blade templateket illesszen
  be `config('simplecrud.blades...')`
  - <head> tagon belül
  - SeimpleCrud content előtt
  - SeimpleCrud content után  
    Ha eltér egyes aloldalaknál, akkor felül lehet írni az általános beállítást. `config('simplecrud.blades.create...')`

### Helper

`DelocalZrt\SimpleCrud\Services\SimpleCrudHelper` ben van / lesz pár közérdekű fg, pl. `getSimpleCrudableAppModelClasses()`

### Modellek hozzáadása CRUD scopehoz

Ha egy Eloquent modelt szeretnénk a CRUD műveletek hatókörébe tenni, akkor az adott eloquent modelnek implementálnia
kell a `DelocalZrt\SimpleCrud\Contracts\CrudModelInterface` interface-t, ahol egy fg-t kell
kidolgoznunk ` public static function getAttributesInfo(): array` , továbbá érdemes beállítani az adott modelnél
a  `public static $title` property-t különben a class nevével lesz hivatkozva rá. Ha nincsenek speciális mezők, más
dolgunk nincs is. Egy tömb elem így épül fel:

`"mező_neve_VAGY_kapcsolatot_megvalósiító_fg_neve|opciók...|" => "Mező megjelenítendő neve"`

a array keyben a mező nevének kell fixen a legelején lenni, opcióknál nem számít a sorrend:

- Első tömb elemnek az ID-nek kell lennie,
- Második tömb elemnek az entitást nevét takaró mezőt ha van (name, title stb.)

#### Ezek a lehetséges opciók az array kulcsokban pipelinnal | elválasztva:
**Típus:**  `custom, hidden, text, password, email, number, textarea, json, texteditor, checkbox, datatime, date, time, select, multiselect, file, image, multifile, multiimage`  

- `select`, `multiselect`  egy-a-többhöz és több-a-többhöz adatbázis kapcsolatokat jelentenek, pl `select.attrSchoolDegree` (`attrSchoolDegree()` => belongsTo-t megvalósító method neve) ehhez kelleni fog még
  select és multiselect feltöltése: `select.Modelneve` (pl. `select.AttrSchoolDegree` (ilyenkor megnézi, hogy van-e `name` vagy `title` mező, ha nincs, akkor az ID-t írja ki az select option-ökbe) lsd lenti példák.
- `custom` esetében nem fog form elemet készíteni a create és update-nál, listenerrel kell megcsinálnunk, ha kell. lsd lenti példák

**Megjelenés:**  `all, index, show, update, create`  
**Kötelező mező?** `required`  
**file, image további opciók:**

- app/cvs/ => (megadhatjuk, hogy a storage mappán belül, hova mentse a fájlt, legalább egy "/" jelnek kell benne lennie praktikusan a végén)
- 640x480 => (image típus esetbében megadhatjuk, hogy mekkorára méretezze a képet mentés előtt)
- thumb100x100 => thumb méretét lehet meghatározni (ugyanoda menti thumb_FÁJLNEVE néven a thumb képeket)
- keepfilename => (maradjon-e a fájl neve, alapértelmezetten új egyedi fájlnevet generál)
- multifile és multiimage mezőnél csak egyenként lehet feltölteni a fájlokat, és csak akkor ha már létezik ID! (maga a mező json, ahol tömbben a fájlok lokációit menti)
- multifile és multiimage mezőnél az opcióknál megadott mappán belül létrehoz az entitás ID nevével egy almappát és abba menti a fájlokat

**Többnyelvűség:** `multilang`
- Csak text, textarea, texteditor -nál
- Ebben az esetben a configban beállított nyelvek szerint menti a (config(simplecrud.multilang_languages)) menti az adott mezőt
- `<!-- LANG_hu_LANG --> Magyar Szöveg <!-- LANG_en_LANG --> Angol szöveg` stb módon választja szét a mezőn belül a nyelveket, tehát végig egy mezőben mardadunk
- ha a configban a `multilang_all_text`, akkor nem kell egyenként beállítnai a `multilang` opciót, automatikusan többnyeévű lesz minden text/textarea/texteditor
- ha kizárólag a főnyelv van csak kitöltve, akkor nem fogja a  <!-- LANG stb elhatárolókat beilleszteni, csak a főnyelvhez tartozó szöveget, így ha valami legacynak állítjuk be, és vannak még helyek, ahol nem a SimpleCrud helperrel iratjuk ki, nem fogja szétszedni, marad ami volt 

#### Példa

Van egy `User\Models\User` nevű eloquent modellünk, implementáljuk a getAttributesInfo() fg-t:

``` 
  public static function getAttributesInfo(): array
    {
        return [
```

**A. típusú mező:** A DB táblában is meglévő mezők (ÉS NEM IDEGEN KULCSOK) első helyen az id legyen, második helyen a
név/title stb

```
            'id' => 'ID',
            'name|text|all|required' => 'Név',
            'email|email|all|required' => 'Email',
            'lastname|json|all|required' => 'Vezetéknév',
            'firstname|text|all|required' => 'Keresztnév',
            'phone|text|all' => 'Telefon',
            'is_newsletter|checkbox|all' => 'Hírlevél',
            'birthday|date|show|create|update' => 'Születésnap',
            'net_wage_demand|number|show|create|update' => 'Bérigény',
            'available_for_headhunter|checkbox|show|create|update' => 'Fejvadászok kereshetnek',
            'cv_file|file|show|create|update|460x460|app/cvs/|keepfilename' => 'Önéletrajz fájl',
            'hide_from_employer|checkbox|show|create|update' => 'Munkáltató elől rejtse el',
            'created_at|datetime-local|show' => 'Létrehozva',
            'updated_at|datetime-local|show' => 'Módosítva',
            'password|password|create|update' => 'Jelszó',
```

**B. típusú mező: BelongsTo** kapcsolatok:  
`select.{EloquentModel}` az a model amiből feltöltjük formban ezeket a selecteket
`mezőneve.name` nél a `mezőneve` a BelongsTo kapcsolatot megvalósító fg neve, a `name` pedig a kapcsolódó
{EloquentModel}-nek az a mezője amit kiíratunk, a form selectnél is ezt írjuk ki.

```
            'userTerm.version|select.UserTerm|show|create|update|required' => 'ÁSZF verzió',
            'userPrivacy.version|select.UserPrivacy|show|create|update|required' => 'Adatvédelmi verzió',
            'netWageDemandCurrency.currency_code|select.Currency|show|create|update' => 'Bérigény pénznem',
            'attrSchoolDegree.name|select.AttrSchoolDegree|index|show|create|update' => 'Legmagasabb iskola',
            'attrWorkExperience.name|select.AttrWorkExperience|show|create|update' => 'Munkatapasztalat',
            'attrHomeOfficeAmount.name|select.AttrHomeOfficeAmount|show|create|update' => 'Home office igény',
            'attrStartWorkIn.name|select.AttrStartWorkIn|show|create|update' => 'Munkába tud állni',
            'city.city|select.City|show|create|update' => 'Város',
```

**C. típusú mező: Many-To-Many**d kapcsolatok:   
a  `multiselect.{EloquentModel}` az a model amiből feltöltjük formban ezeket a multiple selecteket
`mezőneve.name` -nél a `mezőneve` a BelongsToMany kapcsolatot megvalósító fg neve, a `name` pedig a kapcsolódó
{EloquentModel}-nek az a mezője amit kiíratunk, a form selectnél is ezt írjuk ki.

```
            'attrEmplRelats.name|multiselect.AttrEmplRelat|show|create|update' => 'Munka típusa',
            'attrJobCategories.name|multiselect.AttrJobCategory|show|create|update' => 'Kategória',
            'attrWorkplaceDistances.name|multiselect.AttrWorkplaceDistance|show|create|update' => 'Munkahely távolság',
            'attrDriverLicences.name|multiselect.AttrDriverLicence|index|show|create|update' => 'Jogosítványok',

```

**D. típusú mező: Dinamikus mezők**
Olyan mezők, amik nincsenek benne az adatbázisban, és kapcsolatot sem tudnak közvetlenül létrehpzni, csak szimplán
dinamikusan kapcsolódik a modellhez. Ezek a dinamikus mezől lehetnek teljesen függetlenek az adott entitástól,
tetszőleges tartalommal is fel lehet tölteni, ill ha mentésnél is használjuk, akkor jellemzően a bonyolultabb adatbázis
kapcsolatokat oldjuk itt meg vele lsd lejjeb.

- **Általános megoldás**, ahol nem kell listenert használnunk, hanem Modellen belül megoldjunk mindent ehhez az adott
  modelben kell definálni a `get{AttributeName}Attribute()` és ha mentésnél is kell akkor
  a `set{AttributeName}Attribute($val)` függvényeket ilyenkor a `$model->mező_neve` ezeket a magic fg-eket fogja
  meghívni. a type lehet bármi (`text, checkbox stb`) viszont ha custom-re állítjuk, akkor el kell kapnunk a
  ´`CrudBeforeRenderFormEvent-et` és ott a
  `$event->getForm()->setField('mezőneve', (string)$tetszőlegesCucc);` vel tudjuk hozzáadmi a html mezőt
- **Alternatív megoldás**: Listenerrel töltjöük fel tartalommal, ilyenkor nem kell semmilyen fg-t definiálni a Modelben,
  hanem listenerrel feltöltjük ezt a mezőt, ha mentésnél is kell, akkor a before save listenerekkel is el lehet kapni a
  mezőt a post requestből, és mivel itt nem definiáltuk a set{AttributeName}Attribute($value) fg, át fogja ugorni az
  entitás mentésénél.                  
  **PL. languages:** itt nem csak hogy Many to many van, hanem a kapcsolótábla több mezőt is tartalmaz (language,
  language level)
  és mind a kettőt egyenként ki kell választani select mezőben, majd összefűzni, hogy egy darab form input mezőbe
  küldjük el (name=languages), majd amikor mentjük, a mentési műveletnél mivel `$user->languages = $value` van, ami
  egyenlő `User::setLanguagesAttribute($value)` -val, így ez dolgozza meg az inputban megadott összefűzött értéket pl (
  343|3434)  és menti ahova kell.

```       
            'languages|custom|index|show|create|update' => 'Nyelvek', 

        ];
    }
```

### Eventek / Listenerek

A megjelenés testreszabása mellett a logikába is bele tudunk nyúlni, minden érdemi adathoz hozzáférünk. Ehhez Létre kell
hoznunk az `App\SimpleCrud` mappában az eloquent Model nevével megegyező php fájlt, ami lényegében egy
**listener**, ennek implementálnia kell a `DelocalZrt\SimpleCrud\Contracts\SimpleCrudListenerInterface`. Itt lehet
elkapni minden eventet futás közben, és minden érdemi adatot módosítani/törölni/hozzáadni, és az eseményekhez további
műveletet hozzáfűzni, ha kell.

A publikálás során létrejön a `App\SimpleCrud\SimpleCrudListener` listener, ami entitástól függetlenül figyeli az összes eventet és
entitás listenerek előtt kapja el őket. Itt kell az általános dolgokat intézni, pl. autentikáció ellenőrzés, logolás stb 

Ezek az eventek triggerelődnek a megfelelő időben:

- CrudPermissionEvent
- CrudPreparingDatatableEvent
- CrudPreparingQueryBuilderForDatatableEvent
- CrudBeforeSendRowsToDatatableEvent
- CrudBeforeShowEntityEvent
- CrudBeforeRenderFormEvent
- CrudBeforeSaveEvent
- CrudAfterSaveEvent
- CrudAfterCreatedEvent
- CrudAfterUpdatedEvent
- CrudBeforeDeleteEvent
- CrudAfterDeleteEvent

Minden esetben rendelkezésre áll egy `CrudEvent` objektum, ami tartalamazza a

- EloquentModelClass
- action (index, show, create, update, delete)
- id (ha van)
- entity objektum (ha van)

A listenerek kidolgozását lsd: `App\\SimpleCrud\\SimpleCrudExample`, ahol részletesen be van mutatva, milyen lehetőségek
vannak az egyes Eventekkel kapcsolatban.

### Jogosultságkezelés

Az Event/Listener rész érintette ezt a részt is, a listenerekben a `checkPermission()`  fg-nyel tudjuk ellenőrizni a
jogosultságot az egyes `App\SimpleCrud\{EloquentModel}` nál is, de előtte minden esetben lefut
a `App\SimpleCrud\SimpleCrudListener::checkPermission()` fg is, így ide az általános ellenőrzéseket kell tenni,
(be van e jelentkezve stb.), természetesen ennél a listenernél is rendelkezésre áll a `CrudEvent` objektum.

### Action-ök

A `App\\SimpleCrud\{ModelNeve}` Listenerben lehetőségünk van minden nézethez (`index, show, create/update`) action
gombot hozzáadni a toolbarhoz. Két féle action-t tudunk definiálni: Submit button action, Link action.

#### Submit button action

Ezzel a gombbal az adott oldalhoz kapcsolódó ID-t (index oldalon a kiválasztott ID-ket) ÉS a látható form elemeket lehet
elküldeni egy végpontra, ahol fel lehet ezeket az adatokat dolgozni.

`->addAction(['name' =>'Action neve','action' => '/végpont', 'warning' => 'Biztos?', 'icon' => 'flash'  ]);`

Ha **index** oldalról jön az action, és ott ki van jelölve minden elem, akkor nem fogja az összes ID-t elküldeni, hanem
csak egy jelzést, hogy minden elem ki lett jelölve (check-all), ezért minden indexről felől érkező action requestnél így
szűrjük le az küldött ID-ket:   
`$ids = Datatable::getFilteredIds(User::class, $request->all());`

Sikeres feldolgozás esetén a controllerben vissza lehet sikeres üzenettel irányítani:

```
return back()->with(['simplecrudSuccess'=> 'Sikerese feldolgozás!'])
return back()->with(['simplecrudError'=> 'Hiba.....!'])` 
```

vagy

```
return Redirect::route('simplecrud-index', ['eloquentClass' => 'User'])->with(['simplecrudSuccess' => 'Sikeres feldolgozás!']);
return Redirect::route('simplecrud-index', ['eloquentClass' => 'User'])->with(['simplecrudError' => 'Hiba...!']);
```

A feldolgozás során lehetőségünk van dobni `UserCanSeeException` -t és `SimpleCrudPermissionDeniedException` -t, amikhez
ha írunk szöveget, az adott SimpleCrud oldal hibasávjában meg fog jelenni, egyébként meg az alapértelmezett
hibaszövegek.

##### Csoportos műveletek kialakítása

Lehetőségünk van az index nézetet úgy alakítani, hogy ott csoportosan tudjuk az adatokat módosítani custom végponton.
Fontos, ezt csak Select típusú datatablénél csináljuk, mert csak a látható form elemeket fogja elküldeni, és a select
nézetnél ugye nincs lapozás.  
Ehhez az `App\SimpleCrud\{EloquentModel}` listenerben így kell alakítani a datatable-t.

```
public function preparingDatatable(CrudPreparingDatatableEvent $event): void
{
    $dt = $event->getDatatable();
    $dt->addAction([
                'name' => 'Gombon a név',
                'action' => '/vegpont',
                'warning' => 'Biztos ezt meg azt fogod csinálni?',
                'icon' => 'flash'
                ])
    
    $dt ->setSelectedIds([1, 4, 6]); //ki tudunk választani előre elmeket
    $dt->setOperations(['languages']); //a fejlécbe kitesz egy operator(+, -, *, stb.) ÉS operandus input mezőt az adott oszlopokhoz.
    $dt->setTypeToSelect();
    $dt->disableChooseType(); //ne lehessen átkapcsolni checkbox nézetre
}

(....)

public function beforeSendRowsToDatatable(CrudBeforeSendRowsToDatatableEvent $event): void 
{
        $rows->transform(function ($row, $key) {
            $row['phone'] = '<input type="text" name="phone[' . $row['id'] . ']" value="' . $row['phone'] . '">';
            (...)
            return $row;
        });
}

```

Ha szükségünk van az alapértelemezett (checkboxos) index oldalra is meg egy ilyenre is, akkor akár GET query
paraméterekkl is lehet üzenni a listenernek, hogy hogy rakja össze a datatablét, vagy a `delocalzrt\datatable` modulban
leírt módon lehet SimpleCrudtól függetlenük tetszőleges datatable-t kialakítani.

#### Link action

Ezzel a gombbal egy sima link gombot lehet hozzáadni a toolbar-hoz (ezért van `href` és nem `action`)
`->addAction(['name' =>'Action neve','href' => '/végpont', 'icon' => 'flash'  ]);`

### Hibakezelés

Az exceptionöket `APP_DEBUG=false` módban nem mutatja, hanem csak logolja a `simplecrud-{datum}.log` (daily) ba, és a
log azonosító számot kiírja hibasávba, hogy könnyebben lehessen keresni. Ha valamilyen
listenerben `DelocalZrt\SimpleCrud\Exceptions\UserCanSeeException` vagy `SimpleCrudPermissionDeniedException`-t dobunk,
akkor az exception `message`-t hibaüzenetben ki fogja írni kulturáltan a hibasávba a usernek vagy ha nincs szöveg hozzá,
akkor az alapértelemezett:
**Hiba lépett fel a művelet során!**
**Nincs jogosultság az adott művelethez**.
