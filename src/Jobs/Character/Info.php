<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Jobs\Character;

use Seat\Eveapi\Jobs\AbstractCharacterJob;
use Seat\Eveapi\Models\Character\CharacterInfo;

/**
 * Class Info.
 * @package Seat\Eveapi\Jobs\Character
 */
class Info extends AbstractCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/';

    /**
     * @var int
     */
    protected $version = 'v4';

    /**
     * @var array
     */
    protected $tags = ['character'];

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        $character_info = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($character_info->isCachedLoad() && CharacterInfo::find($this->getCharacterId())) return;

        CharacterInfo::firstOrNew(['character_id' => $this->getCharacterId()])->fill([
            'name'            => $character_info->name,
            'description'     => $character_info->optional('description'),
            'birthday'        => $character_info->birthday,
            'gender'          => $character_info->gender,
            'race_id'         => $character_info->race_id,
            'bloodline_id'    => $character_info->bloodline_id,
            'ancestry_id'    => $character_info->optional('ancestry_id'),
            'security_status' => $character_info->optional('security_status'),
        ])->save();
    }
}
