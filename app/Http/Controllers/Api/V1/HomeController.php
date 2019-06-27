<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\StandardCharacter;
use App\Character;
use App\UserTag;
use App\User;
use App\Header;
use App\Value;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only([
            'getStandardCharacters',
            'getArrayCharacters',
            'delete',
            'addHeader',
            'deleteHeader',
            'getHeaders',
            'store',
            'storeCharacter',
            'getUserTags',
            'createUserTag',
            'getCharacter',
            'removeUserTag',
            'storeMatrix',
            'deleteHeader',
            'changeTaxon',
            'addMoreColumn',
            'addCharacter',
            'updateValue',
            'addStandardCharacter',
            'removeAll',
            'removeAllStandard',
            'updateCharacter',
        ]);
    }

    public function getHeaders() {
        return Header::where('user_id', Auth::id())
            ->orWhere('user_id', NULL)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getValuesByCharacter()
    {
        $user = User::where('id', '=', Auth::id())->first();
        $username = explode('@', $user['email'])[0];

        $allCharacters = Character::where('username', 'like', '%' . $username)->orderBy('standard_tag', 'ASC')->get();
        $headers = $this->getHeaders();
        $characters = [];
        foreach ($allCharacters as $eachCharacter) {
            $value_array = [];
            foreach ($headers as $header) {
                if ($value = Value::where(['character_id'=>$eachCharacter->id, 'header_id'=>$header->id])->first()) {
                    $value->username = $eachCharacter->username;
                    $value->unit = $eachCharacter->unit;
                    $value->summary = $eachCharacter->summary;
                    array_push($value_array, $value);
                }

            }
            array_push($characters, $value_array);
        }

        return $characters;
    }

    public function getArrayCharacters() {
        $user = User::where('id', '=', Auth::id())->first();
        $username = explode('@', $user['email'])[0];

        $arrayCharacters = [];
        if (Character::where('username', 'like', '%' . $username)->first()) {
            $arrayCharacters = Character::where('username', 'like', '%' . $username)->orderBy('standard_tag', 'ASC')->get();
        }
        foreach ($arrayCharacters as $c) {

            $usageCount = Value::where('character_id', '=', $c->id)
                ->where('header_id', '>=', 2)
                ->where('value', '<>', '')
                ->count();
            $c->usage_count = $usageCount;
        }
        return $arrayCharacters;
    }

    public function getDefaultCharacters() {
        $standardCharacters = StandardCharacter::all()->toArray();
        $userCharacters = Character::where('standard', '=', 0)->where('username', 'not like', 'onlyUsed by%')->get()->toArray();
        $defaultCharacters = array_merge($standardCharacters, $userCharacters);

        return $defaultCharacters;
    }

    public function removeString($string, $compareStr) {
        $string = str_replace($compareStr, '', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9, \-]/', '', $string); // Removes special chars.

        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }


    public function getStandardCharacters() {
        $standardCharacters = StandardCharacter::all();
        $userCharacters = Character::where('standard', '=', 0)->where('username', 'not like', 'onlyUsed by%')->get();
        foreach ($standardCharacters as $eachCharacter) {
            $character = Character::where('name', '=', $eachCharacter->name)->first();
            if ($character) {
                $usageCount = count(explode(',', $character->username)) - 1;
                $eachCharacter->usage_count = $usageCount;
            }

        }

        foreach ($userCharacters as $eachCharacter) {
            if (StandardCharacter::where('name', '=', $eachCharacter->name)->first()) {
                $character = Character::where('name', '=', $eachCharacter->name)->first();
                $usageCount = count(explode(',', $character->username)) - 1;
                $eachCharacter->usage_count = $usageCount;
            } else {
                $character = Character::where('name', '=', $eachCharacter->name)->first();
                $usageCount = count(explode(',', $character->username));
                $eachCharacter->usage_count = $usageCount;
            }
        }

        $standardCharacters = $standardCharacters->toArray();
        $userCharacters = $userCharacters->toArray();
        $result = array_merge($standardCharacters, $userCharacters);

        return $result;
    }

    public function getUserTags(Request $request, $userId) {
        $userTags = UserTag::where('user_id', '=', $userId)->get();

        return $userTags;
    }

    public function createUserTag(Request $request) {
        $userTag = new UserTag([
            'tag_name' => $request->input('user_tag'),
            'user_id' => $request->input('user_id')
        ]);
        $userTag->save();

        return $userTag;
    }

    public function removeUserTag(Request $request) {
        $userTag = UserTag::where([
            ['tag_name', '=', $request->input('user_tag')],
            ['user_id', '=', $request->input('user_id')],
        ])->delete();

        $userTags = UserTag::where('user_id', '=', $request->input('user_id'))->get();
        return $userTags;
    }

    public function deleteHeader(Request $request, $headerId) {
        $headers = Header::where('id', '=', $headerId)->delete();
        $values = Value::where('header_id', '=', $headerId)->delete();

        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues
        ];

        return $data;
    }
    public function changeTaxon(Request $request, $taxon) {
        $user = User::where('id', '=', Auth::id())->first();
        $user->taxon = $taxon;
        $user->save();

        $data = [
            'taxon' => $user->taxon
        ];

        return $data;
    }

    public function storeCharacter(Request $request) {
        $character = new Character([
            'name' => $request->input('name'),
            'method_from' => $request->input('method_from'),
            'method_to' => $request->input('method_to'),
            'method_include' => $request->input('method_include'),
            'method_exclude' => $request->input('method_exclude'),
            'method_where' => $request->input('method_where'),
            'unit' => $request->input('unit'),
            'standard' => $request->input('standard'),
            'creator' => $request->input('creator'),
            'username' => $request->input('username'),
            'usage_count' => $request->input('usage_count'),
            'show_flag' => $request->input('show_flag'),
            'standard_tag' => $request->input('standard_tag'),
            'summary' => $request->input('summary'),
        ]);

        $character->save();

        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $returnDefaultCharacters = $this->getDefaultCharacters();

        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
            'defaultCharacters' => $returnDefaultCharacters
        ];

        return $data;

    }
    public function getCharacter(Request $request, $userId) {
        $user = User::where('id', '=', $userId)->first();
        $username = explode('@', $user['email'])[0];

        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $returnTaxon = $user->taxon;
        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
            'taxon' => $returnTaxon
        ];

        return $data;
    }

    public function deleteCharacter(Request $request, $userId, $characterId) {
        $user = User::where('id', '=', Auth::id())->first();
        $username = explode('@', $user['email'])[0];
        if (Character::where('standard_tag', '=', Character::where('id', '=', $characterId)->first()->standard_tag)
                ->where('username', 'like', '%' . $username
                )->count() < 2) {
            UserTag::where('tag_name', '=', Character::where('id', '=', $characterId)->first()->standard_tag)
                ->where('user_id', '=', Auth::id())
                ->delete();
        }


        $character = Character::where('id', '=', $characterId)->delete();
        if (Value::where('character_id', '=', $characterId)->first()) {
            Value::where('character_id', '=', $characterId)->delete();
        }


        $characters = Character::where('username', 'like', '%' . $username)->orderBy('standard_tag', 'ASC')->get();

        $usedCharacters = Character::where('username', 'like', '%' . $username . ',%')->get();
        foreach ($usedCharacters as $usedCharacter) {
            $usedCharacter->username = $this->removeString($usedCharacter->username, $username . ',');
            $usedCharacter->save();
        }

        if (!Character::where('username', 'like', '%' . $username)->first()) {
            Header::where('user_id', '=', Auth::id())->delete();
        }

        $standardCharacters = StandardCharacter::all()->toArray();
        $userCharacters = Character::where('standard', '=', 0)->get()->toArray();
        $defaultCharacters = array_merge($standardCharacters, $userCharacters);



        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $returnUserTags = UserTag::where('user_id', '=', Auth::id())->get();
        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
            'userTags' => $returnUserTags,
            'defaultCharacters' => $defaultCharacters
        ];

        return $data;
    }

    public function storeMatrix(Request $request) {
        $user = User::where('id', '=', $request->input('user_id'))->first();
        $username = explode('@', $user['email'])[0];
        $characters = Character::where('username', 'like', '%' . $username)->get();
        $columnCount = (int)$request->input('column_count');
        $user->taxon = $request->input('taxon');
        $user->save();
        $previousHeaderCount = Header::where('user_id', '=', $request->input('user_id'))->count();
        if ($previousHeaderCount < $columnCount) {
            for ($i = 0; $i < ($columnCount - $previousHeaderCount); $i++) {
                $header = Header::create([
                    'user_id' => $request->input('user_id'),
                    'header' => 'Specimen' . ($previousHeaderCount + $i + 1)
                ]);
            }
        }

        $headers = Header::where('user_id', '=', $request->input('user_id'))->get();

        foreach ($characters as $eachCharacter) {
            $value = Value::where('character_id', '=', $eachCharacter->id)
                ->where('header_id', '=', 1)
                ->first();
            if ($value == null) {
                Value::create([
                    'character_id' => $eachCharacter->id,
                    'header_id' => 1,
                    'value' => $eachCharacter->name
                ]);

            }
            foreach($headers as $header) {
                $value = Value::where('character_id', '=', $eachCharacter->id)
                    ->where('header_id', '=', $header->id)
                    ->first();
                if ($value == null) {
                    Value::create([
                        'character_id' => $eachCharacter->id,
                        'header_id' => $header->id,
                        'value' => ''
                    ]);
                }
            }
        }

        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $returnTaxon = $user->taxon;
        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
            'taxon' => $returnTaxon
        ];

        return $data;
    }

    public function addMoreColumn(Request $request, $columnCount) {
        $user = User::where('id', '=', Auth::id())->first();
        $username = explode('@', $user['email'])[0];
        $oldHeaderCount = Header::where('user_id', '=', Auth::id())->count();
        $characters = Character::where('username', 'like', '%' . $username)->get();
        for ($i = 0; $i < ($columnCount - $oldHeaderCount); $i++) {
            for ($j = 0; $j < $columnCount; $j++) {
                if (!Header::where('header', '=', ('Specimen' . ($j + 1)))->where('user_id', '=', Auth::id())->first()) {
                    $header = Header::create([
                        'user_id' => Auth::id(),
                        'header' => 'Specimen' . ($j + 1)
                    ]);
                    foreach ($characters as $eachCharacter) {
                        Value::create([
                            'character_id' => $eachCharacter->id,
                            'header_id' => $header->id,
                            'value' => ''
                        ]);
                    }
                    break;
                }
            }
        }

        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $returnTaxon = $user->taxon;
        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
            'taxon' => $returnTaxon
        ];

        return $data;
    }

    public function addCharacter(Request $request) {
        $user = User::where('id', '=', Auth::id())->first();
        $username = explode('@', $user['email'])[0];

        $character = new Character([
            'name' => $request->input('name'),
            'method_from' => $request->input('method_from'),
            'method_to' => $request->input('method_to'),
            'method_include' => $request->input('method_include'),
            'method_exclude' => $request->input('method_exclude'),
            'method_where' => $request->input('method_where'),
            'unit' => $request->input('unit'),
            'standard' => $request->input('standard'),
            'creator' => $request->input('creator'),
            'username' => $request->input('username'),
            'usage_count' => $request->input('usage_count'),
            'show_flag' => $request->input('show_flag'),
            'standard_tag' => $request->input('standard_tag'),
            'summary' => $request->input('summary'),
        ]);

        $character->save();
        $headers = Header::where('user_id', '=', Auth::id())->get();

        $value = Value::create([
            'header_id' => 1,
            'character_id' => $character->id,
            'value' => $character->name
        ]);
        foreach ($headers as $eachHeader) {
            $value = Value::create([
                'header_id' => $eachHeader->id,
                'character_id' => $character->id,
                'value' => ''
            ]);
        }

        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $returnDefaultCharacters = $this->getDefaultCharacters();
        $returnTaxon = $user->taxon;
        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
            'taxon' => $returnTaxon,
            'defaultCharacters' => $returnDefaultCharacters,
        ];

        return $data;
    }

    public function updateValue(Request $request) {
        $value = Value::where('id', '=', $request->input('id'))->first();

        $v = $request->input('value');
        $value->value = $v;
//        if (is_numeric($v)) {
//            $value->value = $v;
//        } else {
//            $varr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$v);
//            if (count($varr)==2 && is_numeric($varr[0])) {
//                $c = Character::find($value->character_id);
//                if ($c->unit == $varr[1]) {
//                    $value->value = $varr[0];
//                } else {
//                    return ['error_input' => 1];
//                }
//            }
//        }

        $value->save();

        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $data = [
            'error_input' => 0,
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
        ];

        return $data;
    }

    public function addStandardCharacter(Request $request) {
        $standardCharacters = $request->all();

        foreach ($standardCharacters as $eachCharacter) {
            $character = new Character([
                'name' => $eachCharacter['name'],
                'method_from' => $eachCharacter['method_from'],
                'method_to' => $eachCharacter['method_to'],
                'method_include' => $eachCharacter['method_include'],
                'method_exclude' => $eachCharacter['method_exclude'],
                'method_where' => $eachCharacter['method_where'],
                'unit' => $eachCharacter['unit'],
                'standard' => $eachCharacter['standard'],
                'creator' => $eachCharacter['creator'],
                'username' => $eachCharacter['username'],
                'usage_count' => $eachCharacter['usage_count'],
                'show_flag' => $eachCharacter['show_flag'],
                'standard_tag' => $eachCharacter['standard_tag'],
                'summary' => $eachCharacter['summary'],
            ]);

            $character->save();

            if (!UserTag::where('user_id', '=', Auth::id())->where('tag_name', '=', $eachCharacter['standard_tag'])->first()) {
                UserTag::create([
                    'user_id' => Auth::id(),
                    'tag_name' => $eachCharacter['standard_tag']
                ]);
            }
        }

        $returnCharacters = $this->getArrayCharacters();

        return $returnCharacters;
    }

    public function removeAllStandard(Request $request) {
        $user = User::where('id', '=', Auth::id())->first();
        $username = explode('@', $user['email'])[0];

        $characters = Character::where('username', 'like', '%' . $username)->where('standard', '=', 1)->get();
        foreach($characters as $eachCharacter) {
            if (Value::where('character_id', '=', $eachCharacter->id)->first()) {
                Value::where('character_id', '=', $eachCharacter->id)->delete();
            }
            if (Character::where('standard_tag', '=', $eachCharacter->standard_tag)->where('username', 'like', '%' . $username)->count() < 2) {
                UserTag::where('user_id', '=', Auth::id())->where('tag_name', '=', $eachCharacter->standard_tag)->delete();
            }
            $eachCharacter->delete();
        }
        if (!Character::where('username', '=', $username)->first()) {
            if (Header::where('user_id', '=', Auth::id())->first()) {
                Header::where('user_id', '=', Auth::id())->delete();
            }
        }

        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
        ];

        return $data;

    }

    public function showTabCharacter(Request $request, $tabName) {
        $user = User::where('id', '=', Auth::id())->first();
        $username = explode('@', $user['email'])[0];

        $allCharacters = Character::where('username', 'like', '%' . $username)->get();
        $characters = Character::where('standard_tag', '=', $tabName)->where('username', 'like', '%' . $username)->get();

        foreach ($allCharacters as $eachCharacter) {
            $eachCharacter->show_flag = false;
            $eachCharacter->save();
        }
        foreach ($characters as $character) {
            $character->show_flag = true;
            $character->save();
        }

        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
        ];

        return $data;
    }

    public function removeAll(Request $request) {
        $user = User::where('id', '=', Auth::id())->first();
        $username = explode('@', $user['email'])[0];

        $characters = Character::where('username', 'like', '%' . $username)->where('standard', '=', 0)->get();
        foreach($characters as $eachCharacter) {
            if (Value::where('character_id', '=', $eachCharacter->id)->first()) {
                Value::where('character_id', '=', $eachCharacter->id)->delete();
            }
            if (Character::where('standard_tag', '=', $eachCharacter->standard_tag)->where('username', 'like', '%' . $username)->count() < 2) {
                UserTag::where('user_id', '=', Auth::id())->where('tag_name', '=', $eachCharacter->standard_tag)->delete();
            }
            $eachCharacter->delete();
        }

        if (!Character::where('username', 'like', '%' . $username)->first()) {
            Header::where('user_id', '=', Auth::id())->delete();
        }

        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
        ];

        return $data;
    }

    public function updateUnit(Request $request) {
        $character = Character::where('id', '=', $request->input('character_id'))->first();
        $oldUnit = $character->unit;
        if ($character->unit != $request->input('unit')) {
            $character->unit = $request->input('unit');
            $character->save();
            $values = Value::where('character_id', '=', $character->id)->where('header_id', '<>', 1)->get();
            foreach ($values as $value) {
                if ($value->value) {
                    switch ($oldUnit) {
                        case "mm":
                            switch ($request->input('unit')) {
                                case "cm":
                                    $value->value = round((float)$value->value / 10, 2);
                                    $value->save();
                                    break;
                                case "dm":
                                    $value->value = round((float)$value->value / 100, 2);
                                    $value->save();
                                    break;
                                case "m":
                                    $value->value = round((float)$value->value / 1000, 2);
                                    $value->save();
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case "cm":
                            switch ($request->input('unit')) {
                                case "mm":
                                    $value->value = round((float)$value->value * 10, 2);
                                    $value->save();
                                    break;
                                case "dm":
                                    $value->value = round((float)$value->value / 10, 2);
                                    $value->save();
                                    break;
                                case "m":
                                    $value->value = round((float)$value->value / 100, 2);
                                    $value->save();
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case "dm":
                            switch ($request->input('unit')) {
                                case "mm":
                                    $value->value = round((float)$value->value * 100, 2);
                                    $value->save();
                                    break;
                                case "cm":
                                    $value->value = round((float)$value->value * 10, 2);
                                    $value->save();
                                    break;
                                case "m":
                                    $value->value = round((float)$value->value / 10, 2);
                                    $value->save();
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case "m":
                            switch ($request->input('unit')) {
                                case "mm":
                                    $value->value = round((float)$value->value * 1000, 2);
                                    $value->save();
                                    break;
                                case "cm":
                                    $value->value = round((float)$value->value * 100, 2);
                                    $value->save();
                                    break;
                                case "dm":
                                    $value->value = round((float)$value->value * 10, 2);
                                    $value->save();
                                    break;
                                default:
                                    break;
                            }
                            break;
                        default:
                            break;
                    }
                }

            }

        }


        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
        ];

        return $data;
    }

    public function exportDescription(Request $request) {
        $fileName = $request->input('taxon');
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $textLines = explode('<br/>', $request->input('template'));
        $textrun = $section->addTextRun();
        foreach ($textLines as $eachText) {
            $eachText = str_replace('<b>', '', $eachText);
            $eachText = str_replace('</b>', '', $eachText);
            $separatedTexts = explode(':', $eachText);
            if (count($separatedTexts) > 1) {
                $textrun->addText($separatedTexts[0] . ': ', ['bold' => true]);
                $textrun->addText($separatedTexts[1]);
            }
        }
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($fileName . '.docx');

        return array(
            'is_scucess'    =>  1,
            'doc_url'       =>  '/chrecorder/public/' . $fileName . '.docx',
        );
    }

    public function updateSummary(Request $request) {
        $character = Character::where('id', '=', $request->input('character_id'))->first();
        $character->summary = $request->input('summary');
        $character->save();

        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
        ];

        return $data;
    }

    public function updateCharacter(Request $request) {
        $user = User::where('id', '=', Auth::id())->first();
        $username = explode('@', $user['email'])[0];

        $character = Character::where('id', '=', $request->input('id'))->first();

        $oldTag = $character->standard_tag;

        $character->name = $request->input('name');
        $character->method_from = $request->input('method_from');
        $character->method_to = $request->input('method_to');
        $character->method_include = $request->input('method_include');
        $character->method_exclude = $request->input('method_exclude');
        $character->method_where = $request->input('method_where');
        $character->unit = $request->input('unit');
        $character->standard = $request->input('standard');
        $character->creator = $request->input('creator');
        $character->username = $request->input('username');
        $character->usage_count = $request->input('usage_count');
        $character->show_flag = $request->input('show_flag');
        $character->standard_tag = $request->input('standard_tag');
        $character->summary = $request->input('summary');

        $character->save();

        if ($oldTag != $character->standard_tag) {
            if (Character::where('username', 'like', '%' . $username)->where('standard_tag', '=', $oldTag)->first() == null) {
                UserTag::where('tag_name', '=', $oldTag)->where('user_id', '=', Auth::id())->delete();
            }

            if (UserTag::where('tag_name', '=', $character->standard_tag)->where('user_id', '=', Auth::id())->first() == null) {
                $userTag = new UserTag([
                    'tag_name'  =>  $character->standard_tag,
                    'user_id'   =>  Auth::id()
                ]);

                $userTag->save();
            }
        }

        $returnUserTags = UserTag::where('user_id', '=', Auth::id())->get();
        $returnHeaders = $this->getHeaders();
        $returnValues = $this->getValuesByCharacter();
        $returnCharacters = $this->getArrayCharacters();
        $returnDefaultCharacters = $this->getDefaultCharacters();

        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
            'defaultCharacters' => $returnDefaultCharacters,
            'userTags'  => $returnUserTags
        ];

        return $data;
    }
}
