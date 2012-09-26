<?php
/**
 * Skills Model
 * Handles skills
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\database\Query;

use darkcrusader\models\UserModel;
use darkcrusader\models\OuterEmpiresModel;

use darkcrusader\sqlbeans\SkillBean;
use darkcrusader\sqlbeans\SkillCategoryBean;
use darkcrusader\sqlbeans\SkillPrerequisteBean;
use darkcrusader\sqlbeans\LinkedCharacterBean;

class SkillsModel extends Model {

	protected static $modelID = "skills";

	/**
	 * Updates the skills database with any new skills found in the OE database
	 * Should only be run whenever new skills are added to OE
	 */
	public function updateDB() {

		// randomly grab an api key from the table, doesn't matter whos it is, we only want to scrape skill list
		$q = new Query("SELECT");
		$q->limit(1);
		$q->orderby("linked_characters.id", "ASC");
		$lcbs = LinkedCharacterBean::select($q);

		$accessKey = $lcbs[0]->api_key;

		// grab all skills from the api
		$skills = OuterEmpiresModel::getInstance()->getAllSkills(false, $accessKey);

		// sort and store (with dupe check on)
		foreach($skills as $skill) {
			$this->addSkillCategory($skill->category);
			$this->addSkill($skill->name, $skill->description, false, $skill->category);

			if ($skill->prerequiste)
				$this->addSkillPrerequiste(false, $skill->name, false, $skill->prerequiste, $skill->prerequisteLevel);
		}
	}

	/**
	 * Gets a skill category
	 * 
	 * @param int $id skill category id
	 * @param string $name category name
	 * @return SkillCategoryBean skill category or false if no such skill category
	 */
	public function getSkillCategory($id=false, $name=false) {
		
		$q = new Query("SELECT");
		if ($id)
			$q->where("skill_categories.id = ?", $id);

		if ($name)
			$q->where("skill_categories.name = ?", $name);

		$scbs = SkillCategoryBean::select($q, true);

		return ($scbs[0]) ? $scbs[0] : false;
	}

	/**
	 * Get all skill categories
	 * 
	 * @return array array of SkillCategoryBeans
	 */
	public function getAllSkillCategories() {
		return SkillCategoryBean::select(null, true);
	}


	/**
	 * Adds a skill category if it's not already in the DB
	 * 
	 * @param string $name skill category name
	 */
	public function addSkillCategory($name) {
		
		if ($this->getSkillCategory(false, $name))
			return; // dupe

		$scb = new SkillCategoryBean;
		$scb->name = $name;
		$scb->insert();
	}

	/**
	 * Gets a skill
	 * 
	 * @param int $id skill id
	 * @param string $name skill name
	 * @return SkillBean skill
	 */
	public function getSkill($id=false, $name=false) {
	
		$q = new Query("SELECT");
		if ($id)
			$q->where("skills.id = ?", $id);

		if ($name)
			$q->where("skills.name = ?", $name);

		$sbs = SkillBean::select($q, true);

		return ($sbs[0]) ? $sbs[0] : false;
	}

	/**
	 * Get all skills
	 * 
	 * @return array array of SkillBeans
	 */
	public function getAllSkills() {
		return SkillBean::select(null, true);
	}

	/**
	 * Get all skills in a category
	 * 
	 * @param int $category skill category id
	 * @return array array of SkillBeans
	 */
	public function getSkillsInCategory($category) {
		$q = new Query("SELECT");
		$q->where("category_id = ?", $category);

		return SkillBean::select($q, true);
	}

	/**
	 * Adds a skill if it's not already in the DB
	 * 
	 * @param string $name skill name
	 * @param string $description skill description
	 * @param int $categoryId skill category id if known
	 * @param string $categoryName skill category name if id is unknown
	 */
	public function addSkill($name, $description, $categoryId=false, $categoryName=false) {

		if (!$categoryId)
			$categoryId = $this->getSkillCategory(false, $categoryName)->id;

		if ($sb = $this->getSkill(false, $name)) {

			// check if any info is out of date
			if ($sb->description != $description) {
				$update = true;
				$sb->description = $description;
			}

			if ($sb->category_id != $categoryId) {
				$update = true;
				$sb->category_id = $categoryId;
			}

			if ($update)
				$sb->update();

			return;
		}

		$sb = new SkillBean;
		$sb->name = $name;
		$sb->description = $description;
		$sb->category_id = $categoryId;
		$sb->insert();
	}

	/**
	 * Adds a skill prerequiste if it's not already in the DB
	 * 
	 * @param int $skillId main skill id
	 * @param string $skillName main skill name, if id is unknown
	 * @param int $prerequisteSkillId prerequiste skill id
	 * @param string $prerequisteSkillName prerequiste skill name, if id is unknown
	 * @param int $prerequisteSkillLevel required level of prerequiste skill
	 */
	public function addSkillPrerequiste($skillId=false, 
		$skillName=false, $prerequisteSkillId=false, $prerequisteSkillName=false, $prerequisteSkillLevel) {

		if (!$skillId)
			$skillId = $this->getSkill(false, $skillName)->id;

		if (!$prerequisteSkillId) {
			$prerequisteSkillId = $this->getSkill(false, $prerequisteSkillName)->id;

			if (!$prerequisteSkillId)
				return;
		}

		$q = new Query("SELECT");
		$q->where("skill_id = ?", $skillId);
		$q->where("prerequiste_skill_id = ?", $prerequisteSkillId);
		$spbs = SkillPrerequisteBean::select($q);

		if ($spbs[0]) {

			// check if any info is out of date
			if ($spbs[0]->prerequiste_skill_level != $prerequisteSkillLevel) {
				$spbs[0]->prerequiste_skill_level = $prerequisteSkillLevel;
				$spbs[0]->update();
			}

			return;
		}

		$spb = new SkillPrerequisteBean;
		$spb->skill_id = $skillId;
		$spb->prerequiste_skill_id = $prerequisteSkillId;
		$spb->prerequiste_skill_level = $prerequisteSkillLevel;
		$spb->insert();
	}

	/**
	 * Gets the prerequiste skills for a skill
	 * 
	 * @param int $skill skill id to get prerequistes for
	 * @return array array of SkillPrerequisteBeans
	 */
	public function getPrerequisteSkillsForSkill($skill) {
		$q = new Query("SELECT");
		$q->where("skill_id = ?", $skill);
		$spbs = SkillPrerequisteBean::select($q, true);
		return $spbs;
	}

	/**
	 * Gets the skills unlocked via upgrading a skill
	 * 
	 * @param int $skill skill id to get skills unlocked via this skill
	 * @return array array of SkillPrerequisteBeans
	 */
	public function getPrerequisteSkillsThatSkillUnlocks($skill) {
		$q = new Query("SELECT");
		$q->where("prerequiste_skill_id = ?", $skill);
		$q->orderby("prerequiste_skill_level", "ASC");
		$spbs = SkillPrerequisteBean::select($q, true);
		return $spbs;
	}
}
?>