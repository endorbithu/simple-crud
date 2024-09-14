<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 01. 19.
 * Time: 14:37
 */

namespace App\SimpleCrud;


use Attribute\Models\AttrLanguage;
use Attribute\Models\AttrLanguageLevel;
use Attribute\Models\AttrLanguageUser;
use DelocalZrt\SimpleCrud\Contracts\SimpleCrudListenerInterface;
use DelocalZrt\SimpleCrud\Events\CrudAfterCreatedEvent;
use DelocalZrt\SimpleCrud\Events\CrudAfterDeleteEvent;
use DelocalZrt\SimpleCrud\Events\CrudAfterSaveEvent;
use DelocalZrt\SimpleCrud\Events\CrudAfterUpdatedEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeDeleteEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeRenderFormEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeSaveEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeSendRowsToDatatableEvent;
use DelocalZrt\SimpleCrud\Events\CrudBeforeShowEntityEvent;
use DelocalZrt\SimpleCrud\Events\CrudPermissionEvent;
use DelocalZrt\SimpleCrud\Events\CrudPreparingDatatableEvent;
use DelocalZrt\SimpleCrud\Events\CrudPreparingQueryBuilderForDatatableEvent;
use DelocalZrt\SimpleCrud\Exceptions\SimpleCrudPermissionDeniedException;
use DelocalZrt\SimpleCrud\Exceptions\UserCanSeeException;
use Illuminate\Support\Facades\Hash;

/**
 *
 * A Class nevének az Eloquent model nevének kell lennie
 *
 * Class ExampleListener
 * @package App\SimpleCrud
 */
class SimpleCrudExample implements SimpleCrudListenerInterface
{


    public function checkPermission(CrudPermissionEvent $event): void
    {
        //create, index, update, show
        if ($event->getCrudEvent()->getAction() == 'create') {
            //if (valami) akkor ne engedjen létrehozni
            throw new SimpleCrudPermissionDeniedException('Nincs jogod usert létrehozni!');
        }

        if ($event->getCrudEvent()->getAction() == 'update') {
            //csak a sajátodat lehessen módosítani
            $admin = false;
            if (!$admin && $event->getCrudEvent()->getEntity()->id != auth()->id()) {
                throw new SimpleCrudPermissionDeniedException('Csak a saját useredet módosíthatod!');
            }
        }
        //stb.

    }


    public function preparingDatatable(CrudPreparingDatatableEvent $event): void
    {
        $dt = $event->getDatatable();


        $dt->addAction([
            'name' => 'Gombon a név',
            'icon' => 'ignition', //glyphicon glyphicon-.....    https://getbootstrap.com/docs/3.3/components/
///////////////////////////////////////////////////////
            //VAGY (XOR)
            // sima URL
            'href' => '/valamiurl',
/////////////////////////////////////////////////////////////////////////////////////
            //VAGY (XOR)
            // form submit button, POST methoddal, és elküldi az összes látható elemet az ID-kkel együtt lsd example végpont
            // + a modalba lehet írni a figylemeztető szöveget is
            //FONTOS! csak a látható fieldek lesznek elküldve a végponthoz, tehát a checkboxos típus, mivel lapozós nem lesz megfelelő,
            // ha a sorokba is teszünk fieldeket, ilyenkor mindig setTypeToSelect -et haszunáljunk és disableChooseType()
            //LSD többit readme action végpontok
            'action' => '/vegpont',
            'warning' => 'Biztos ezt meg azt fogod csinálni?'
///////////////////////////////////////////////////////

        ]);


        //ki is nullázhatjuk, tehát semmilyen gombot nem fog mutatni
        //$dt->setAction([]);

        // ha van az datatable körül form csinálva, akkor beállíthatjuk, hogy ne készítsen a datatable magánbak form mezőt, és így a mezőket használhatja az a form amit köré írtunk
        $dt->disableFormTag();
        $dt->enableFormTag();

        //ez ís csak selectnél van, előre kiválasztja és ezeket a sorokat rajzolja ki csak
        $dt->setSelectedIds([1, 4, 6]);

        //melyik mezőnél tegyen ki operation-t a fejlévbe, azok az adatok is mennek a formmmal együtt,
        $dt->setOperations(['languages']);

        $dt->setTypeToSelect();

        $dt->setTypeToCheckbox();
        $dt->setDescription('A SimpleCrud\\Userben megadott description');
        $dt->setToolbarAtBottom();
        $dt->setToolbarAtBottom(false);
        $dt->enableChooseType();
        $dt->disableChooseType();
        $dt->disableCsv();

        $dt->setOrder(['email' => 'asc']);
    }

    public function preparingQueryBuilderForDatatable(CrudPreparingQueryBuilderForDatatableEvent $event): void
    {
        $event->getQueryBuilder()->where('id', '>', 1000);
    }


    public function beforeSendRowsToDatatable(CrudBeforeSendRowsToDatatableEvent $event): void
    {

        $rows = $event->getRows();

        //Fontos, hogy nem map, hanem transform fg, mert ez referencia szerint íra felül
        $rows->transform(function ($row, $key) {
            //csoportos módosítánál csinálhatunk ilyet, de FONTOS, hogy ezt csak select nézetben csináljuk, mert csak a látható
            //form inputokat küldi el!
            //LSD többit readme action végpontok
            $row['phone'] = '<input type="text" name="phone[' . $row['id'] . ']" value="' . $row['phone'] . '">';

            if (isset($row['is_newsletter'])) $row['is_newsletter'] = $row['is_newsletter'] ? 'IGEN' : 'NEM';
            return $row;
        });
    }


    public function beforeShowEntity(CrudBeforeShowEntityEvent $event): void
    {
        //label-el egyöütt az egész html blokk tartozik egy fieldhez a value-t meg az entitásból ki lehet szedni
        $event->getData()->setDescription('Valami html szöveg');
        $event->getData()->setFooter('Valami html footer');
        $event->getData()->setTitle('Felülírhatjuk a címet is');
        $event->getData()->setField('languages', $event->getData()->getField('languages'));
    }


    public function beforeRenderForm(CrudBeforeRenderFormEvent $event): void
    {
        //Itt is egy field = labellel együtt a html field
        $this->addLanguagesToUserForm($event);
        $this->addPasswordField($event);
    }


    public function beforeSave(CrudBeforeSaveEvent $event): void
    {
        //Itt egy mező már nem html blokk, hanem a formon küldött value-kat jelenti már
        $givenPasswd = $event->getData()->getField('password');

        if (!$event->getEntity() && !$givenPasswd) throw new UserCanSeeException('Jelszó kötelező mező!');

        if ($givenPasswd) {
            $event->getData()->setField('password', Hash::make($givenPasswd));
        } else {
            $event->getData()->removeField('password');
        }
    }

    public function afterSave(CrudAfterSaveEvent $event): void
    {
        //create-nél és updatenél is lefut
        //Itt is elérjük a feldolgozott fieldname=>value tömböt, tehát amit megdolgoztunk a beforeSave-ben
        $event->getData()->getFields();
        $event->getEntity();
    }

    public function afterCreated(CrudAfterCreatedEvent $event): void
    {
        //Itt is elérjük a feldolgozott fieldname=>value tömböt, tehát amit megdolgoztunk a beforeSave-ben
        $event->getData()->getFields();
        $event->getEntity();
    }

    public function afterUpdated(CrudAfterUpdatedEvent $event): void
    {

        //Itt is elérjük a feldolgozott fieldname=>value tömböt, tehát amit megdolgoztunk a beforeSave-ben
        $event->getData()->getFields();
        $event->getEntity();
    }


    public function beforeDelete(CrudBeforeDeleteEvent $event): void
    {
        $event->getEntity();
        if ('nem lehet törölni valamiért' && false) {
            throw new UserCanSeeException('Nem lehet törölni az adott entitást');
        }
    }

    public function afterDelete(CrudAfterDeleteEvent $event): void
    {
        //a sikeresen törölt entitásnak még itt el tudjuk kapni ID_ját körbenézhetünk, hol van még esetleg érintettség a db-ben
        $event->getEntity()->id;
    }


    protected function addLanguagesToUserForm(CrudBeforeRenderFormEvent $event)
    {
        $user = $event->getEntity();

        $userLangs = [];
        if ($user && $user->id) {
            foreach (AttrLanguageUser::where('user_id', $user->id)->get() as $item) {
                $userLangs[] = $item->attr_language_id . '|' . $item->attr_language_level_id;
            }
        }

        $out = '<br><label for="languages-select">Nyelvtudás</label>';

        $languageField = AttrLanguage::all()->pluck('name', 'id');
        $languageLevel = AttrLanguageLevel::all()->pluck('name', 'id');
        $out .= '<input type="hidden" name="languages" value="">';

        $out .= '<br><select name="languages[]" id="languages-select" multiple class="form-control">';
        foreach ($languageField as $id => $name) {
            foreach ($languageLevel as $lId => $lname) {
                $idtoSend = $id . '|' . $lId;
                $out .= '<option ' . (in_array($idtoSend, $userLangs) ? ' selected ' : '') . ' value="' . $idtoSend . '">' . $name . ' - ' . $lname . '</option>';

            }
        }

        $out .= '</select>';
        $out .= '<script>$("#languages-select").select2();</script>';

        $event->getData()->setField('languages', $out);
    }

    public function addPasswordField(CrudBeforeRenderFormEvent $event)
    {
        $label = $event->getEntity() ? 'Jelszó módosítás' : 'Jelszó';

        $out = '
<script type="text/javascript">
    function pw_check(input) {
        if (input.value != document.getElementById("pw1").value) {
            input.setCustomValidity("Jelszó nem egyezik");
        } else {
            // input is valid -- reset the error message
            input.setCustomValidity("");
        }
    }
</script>
        ';
        $out .= '<label for="pw1">' . $label . '</label>';

        $out .= '<input type="password" name="password" class="form-control" id="pw1">';
        $out .= '<br><label for="pw2" >Jelszó ismét</label>';

        $out .= '<input type="password" id="pw2" class="form-control" oninput="pw_check(this)" >';


        $event->getData()->setField('password', $out);


    }
}
