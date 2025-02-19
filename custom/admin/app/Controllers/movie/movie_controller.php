<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/MovieModel.php');

class MovieController
{

    private $movieModel;

    public function __construct()
    {
        $this->movieModel = new MovieModel();
    }

    public function index()
    {
        $movie = $this->movieModel->getMovies();

        return $movie;
    }
}
