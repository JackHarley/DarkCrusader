<?php
/**
 * Skills Controller
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;

use darkcrusader\models\SkillsModel;

use hydrogen\view\View;

class SkillsController extends Controller {

	public function index() {
		$this->checkAuth("access_skills");

		View::load('skills/index');
	}

	public function browse() {
		$this->checkAuth("access_skills");

		$sm = SkillsModel::getInstance();

		if ((isset($_GET["category_id"])) && ($_GET["category_id"] != 0)) {
			$skills = $sm->getSkillsInCategory($_GET["category_id"]);
			View::setVar('category_id', $_GET["category_id"]);
		}
		else {
			$skills = $sm->getAllSkills();
		}

		View::load('skills/browse', array(
			"allSkills" => $skills,
			"skillCategories" => $sm->getAllSkillCategories()
		));
	}

	public function skill() {
		$this->checkAuth("access_skills");

		View::load('skills/skill', array(
			"skill" => SkillsModel::getInstance()->getSkill($_GET["id"])
		));
	}
}
?>