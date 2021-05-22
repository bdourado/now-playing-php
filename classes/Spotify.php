<?php

namespace Classes;

use \SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

class Spotify {

    private $accessToken;

    private $api;

    public function __construct()
    {
        $session = new Session(
            $_ENV['SPOTIFY_KEY'],
            $_ENV['SPOTIFY_SECRET_KEY'],
        );

        $session->requestCredentialsToken();
    
        $this->api = new SpotifyWebAPI();
        $this->api->setAccessToken($session->getAccessToken());
    }

    public function search($searchPhrase)
    {
        $searchPhrase = trim(str_replace(Helpers::TRIGGER_WORD, '',$searchPhrase));
        
        $searchItems = explode('-',$searchPhrase);

        if (count($searchItems) !== 2) {
            return false;
        }

        for ($i = 0; $i < 10; $i++) {
            $offSet = $i * Helpers::SPOTIFY_LIMIT;
            $artist = $this->searchArtist($searchItems[0],$offSet);

            if (isset($artist)) {
                break;
            }
        }

        if(empty($artist)){
            return false;
        }

        for ($i = 0; $i < 10; $i++) {
            $offSet = $i * Helpers::SPOTIFY_LIMIT;
            $album = $this->searchAlbum($artist,$searchItems[1], $offSet);
            if(isset($album)){
                $spotifyUrl = Helpers::SPOTIFY_ALBUM_URL . $album->id;
                break;
            }
        }

        return $spotifyUrl;
    }

    public function searchArtist($searchArtist, $offSet)
    {
        $results = $this->api->search(trim($searchArtist), 'artist',[
            'limit'=> Helpers::SPOTIFY_LIMIT,
            'offset' => $offSet
        ]);
        
        $foundArtist = null;
        foreach ($results->artists->items as $artist) {
            if (strtolower($artist->name) === strtolower(trim($searchArtist))){
                $foundArtist = $artist;
                break;
            }
        }
        
        return $foundArtist;
    }

    public function searchAlbum($artist, $searchAlbum, $offSet = 0)
    {
        $albums = $this->api->getArtistAlbums($artist->id,[
            'album_type' => Helpers::SPOTIFY_TYPE,
            'limit'=> Helpers::SPOTIFY_LIMIT, 
            'offset' => $offSet
        ]);

        $foundAlbum = null;
        foreach ($albums->items as $album) {
            $albumName =  preg_replace("/\([^)]+\)/","",$album->name);
            
            if (strtolower($albumName) === strtolower(trim($searchAlbum))) {
                $foundAlbum = $album;
            }
        }

        return $foundAlbum;
    }

}