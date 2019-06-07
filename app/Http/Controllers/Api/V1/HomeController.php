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
            'updateCharacter',
            'addStandardCharacter',
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

        $allCharacters = Character::where('username', '=', $username)->orderBy('standard_tag', 'ASC')->get();
        $headers = $this->getHeaders();
        $characters = [];
        foreach ($allCharacters as $eachCharacter) {
            $value_array = [];
            foreach ($headers as $header) {
                if ($value = Value::where(['character_id'=>$eachCharacter->id, 'header_id'=>$header->id])->first()) {
                    $value->username = $eachCharacter->username;
                    $value->unit = $eachCharacter->unit;
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
        $arrayCharacters = Character::where('username', '=', $username)->orderBy('standard_tag', 'ASC')->get();
        foreach ($arrayCharacters as $c) {

            $usageCount = Value::where('character_id', '=', $c->id)
                ->where('header_id', '>=', 2)
                ->where('value', '<>', '')
                ->count();
            $c->usage_count = $usageCount;
        }
        return $arrayCharacters;
    }

    public function getStandardCharacters() {
        $standardCharacters = StandardCharacter::all();

        return $standardCharacters;
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

        $characters = Character::where('username', '=', $character['username'])->orderBy('standard_tag', 'ASC')->get();

        return $characters;

    }
    public function getCharacter(Request $request, $userId) {
        $user = User::where('id', '=', $userId)->first();
        $username = explode('@', $user['email'])[0];
        $characters = Character::where('username', '=', $username)->orderBy('standard_tag', 'ASC')->get();

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
        $character = Character::where('id', '=', $characterId)->delete();
        if (Value::where('character_id', '=', $characterId)->first()) {
            Value::where('character_id', '=', $characterId)->delete();
        }

        $user = User::where('id', '=', $userId)->first();
        $username = explode('@', $user['email'])[0];
        $characters = Character::where('username', '=', $username)->orderBy('standard_tag', 'ASC')->get();


        return $characters;
    }

    public function storeMatrix(Request $request) {
        $user = User::where('id', '=', $request->input('user_id'))->first();
        $username = explode('@', $user['email'])[0];
        $characters = Character::where('username', '=', $username)->get();
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
        $characters = Character::where('username', '=', $username)->get();
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
        $returnTaxon = $user->taxon;
        $data = [
            'headers' => $returnHeaders,
            'characters' => $returnCharacters,
            'values' => $returnValues,
            'taxon' => $returnTaxon
        ];

        return $data;
    }

    public function updateCharacter(Request $request) {
        $value = Value::where('id', '=', $request->input('id'))->first();

        $v = $request->input('value');

        if (is_numeric($v)) {
            $value->value = $v;
        } else {
            $varr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$v);
            if (count($varr)==2 && is_numeric($varr[0])) {
                $c = Character::find($value->character_id);
                if ($c->unit == $varr[1]) {
                    $value->value = $varr[0];
                } else {
                    return ['error_input' => 1];
                }
            }
        }

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
//        dd($standardCharacters);

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

        $characters = Character::where('username', '=', $username)->where('standard', '=', 1)->get();
        foreach($characters as $eachCharacter) {
            if (Value::where('character_id', '=', $eachCharacter->id)->first()) {
                Value::where('character_id', '=', $eachCharacter->id)->delete();
            }
            if (Character::where('standard_tag', '=', $eachCharacter->standard_tag)->where('username', '=', $username)->count() < 2) {
                UserTag::where('user_id', '=', Auth::id())->where('tag_name', '=', $eachCharacter->standard_tag)->delete();
            }
            $eachCharacter->delete();
        }
        $returnCharacters = $this->getArrayCharacters();

        return $returnCharacters;
    }

    public function showTabCharacter(Request $request, $tabName) {
        $user = User::where('id', '=', Auth::id())->first();
        $username = explode('@', $user['email'])[0];

        $allCharacters = Character::where('username', '=', $username)->get();
        $characters = Character::where('standard_tag', '=', $tabName)->where('username', '=', $username)->get();

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
}
