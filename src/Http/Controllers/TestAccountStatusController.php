<?php

namespace Helious\SeatAccountStatus\Http\Controllers;


use Seat\Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Seat\Eveapi\Models\Character\CharacterSkill;
use Seat\Eveapi\Models\Skills\CharacterSkillQueue;
use Seat\Eveapi\Models\Skills\CharacterAttribute;
use GuzzleHttp\Client;

class EveConstants {
    const SPARE_ATTRIBUTE_POINTS_ON_REMAP = 14;
    const CHARACTER_BASE_ATTRIBUTE_POINTS = 17;
    const MAX_REMAPPABLE_POINTS_PER_ATTRIBUTE = 10;
    const MAX_IMPLANT_POINTS = 5;
    const DOWNTIME_HOUR = 11;
    const DOWNTIME_DURATION = 30;
    const TRANSACTION_TAX_BASE = 0.05;
    const BROKER_FEE_BASE = 0.05;
    const MAX_SKILLS_IN_QUEUE = 50;
    const MAX_ALPHA_SKILL_TRAINING = 5000000;
    const REGION_RANGE = 32767;
}

class TestAccountStatusController extends Controller
{

    /**
     * Show the eligibility checker.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $acountStatus = $this->updateAccountStatus(2115150305);
        dd($acountStatus);
    }

    function updateAccountStatus($characterId, $status = 'Unknown') {
        /// Create a new Guzzle client
        $client = new Client();

        // Fetch the JSON data
        $response = $client->request('GET', 'http://sde.hoboleaks.space/tq/clonestates.json');

        // Check that the request was successful
        if ($response->getStatusCode() == 200) {
            // Decode the JSON data into an associative array
            $data = json_decode($response->getBody(), true);
    
            // Get the character's skills
            $characterSkills = CharacterSkill::where('character_id', $characterId)->get();
    
            // Get the currently training skill
            $currentlyTrainingSkill = CharacterSkillQueue::where('character_id', $characterId)
                ->where('finish_date', '>', now())
                ->orderBy('finish_date', 'asc')
                ->first();
    
            $skillIsTraining = !is_null($currentlyTrainingSkill);
    
            if ($skillIsTraining && $characterSkills->sum('skillpoints_in_skill') > EveConstants::MAX_ALPHA_SKILL_TRAINING) {
                $status = 'Omega';
            } else {
                $likelyAlpha = false;
                foreach ($characterSkills as $skill) {
                    // Is the skill level being limited by alpha status?
                    if ($skill->active_skill_level < $skill->trained_skill_level) {
                        // Active level is being limited by alpha status.
                        $likelyAlpha = true;
                    }
                    // Has the skill alpha limit been exceeded?
                    foreach ($data as $cloneState) {
                        if(!array_key_exists($skill->skill_id, $cloneState['skills'])) continue;
                        if (is_array($cloneState['skills']) && $skill->active_skill_level > $cloneState['skills'][$skill->skill_id] ?? 0) {
                            // Active level is greater than alpha limit, only on Omega.
                            $status = 'Omega';
                            break 2; // Break out of both loops
                        }
                    }
                }
                if ($status == 'Unknown') {
                    if ($likelyAlpha) {
                        $status = 'Alpha';
                    } elseif ($skillIsTraining) {
                        // Try to determine account status based on training time
                        $hoursToTrain = $currentlyTrainingSkill->finish_date->diffInHours($currentlyTrainingSkill->start_date);
                        $secondsToTrain = $currentlyTrainingSkill->finish_date->diffInSeconds($currentlyTrainingSkill->start_date);
                        $spToTrain = $currentlyTrainingSkill->level_end_sp - $currentlyTrainingSkill->level_start_sp;
    
                        if ($secondsToTrain > 8 && $spToTrain > 0) {
                            $spPerHour = $spToTrain / $hoursToTrain;
                            $rate = $this->getOmegaSPPerHour($currentlyTrainingSkill->skill_id) / $spPerHour;
    
                            if ($rate < 1.2 && $rate > 0.8) {
                                $status = 'Omega';
                            } elseif ($rate > 1.1) {
                                $status = 'Alpha';
                            }
                        }
                    }
                }
            }
    
            return $status;
        }
    
        // If the request was not successful, throw an exception
        throw new Exception('Failed to fetch Alpha skills data');
    }
    
    function getOmegaSPPerHour($skillId) {
        // Fetch the skill data from your database or wherever you store it
        $skill = Skill::find($skillId);
    
        // Check that the skill exists
        if ($skill) {
            // Get the primary and secondary attributes
            $primaryAttr = $this->getAttribute($skill->primary_attribute);
            $secondaryAttr = $this->getAttribute($skill->secondary_attribute);
    
            // Calculate and return the SP per hour
            return $primaryAttr * 60.0 + $secondaryAttr * 30.0;
        }
    
        // If the skill does not exist, throw an exception
        throw new Exception("Skill not found: $skillId");
    }
    
    function getAttribute($attribute) {
        // Fetch the character's attributes from the database
        $characterAttributes = CharacterAttribute::where('character_id', $this->characterId)->first();
    
        // Check that the attributes exist
        if ($characterAttributes) {
            // Return the attribute value
            switch ($attribute) {
                case 'intelligence':
                    return $characterAttributes->intelligence;
                case 'perception':
                    return $characterAttributes->perception;
                case 'charisma':
                    return $characterAttributes->charisma;
                case 'willpower':
                    return $characterAttributes->willpower;
                case 'memory':
                    return $characterAttributes->memory;
                default:
                    return null;
            }
        }
    
        // If the attributes do not exist, throw an exception
        throw new Exception("Attributes not found for character: $this->characterId");
    }


}
