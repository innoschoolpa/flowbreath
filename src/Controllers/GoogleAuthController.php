<?php

namespace App\Controllers;

use App\Models\User;
use App\Core\Session;
use App\Core\Config;
use App\Core\Logger;
use App\Core\Database;
use App\Core\Request;
use Google_Client;
use Google_Service_Oauth2;

class GoogleAuthController extends BaseController
{
    private $client;
    private $userModel;
    private $session;
    private $logger;
    private $db;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->db = Database::getInstance();
        $this->userModel = new User($this->db);
        $this->session = Session::getInstance();
        $this->logger = Logger::getInstance();
        $this->initializeGoogleClient();
    }

    private function initializeGoogleClient()
    {
        try {
            $this->client = new Google_Client();
            $this->client->setClientId(Config::get('google.client_id'));
            $this->client->setClientSecret(Config::get('google.client_secret'));
            $this->client->setRedirectUri(Config::get('google.redirect_uri'));
            $this->client->addScope('email');
            $this->client->addScope('profile');
            $this->client->setPrompt('select_account');
            $this->client->setIncludeGrantedScopes(true);
        } catch (\Exception $e) {
            $this->logger->error('Google client initialization failed: ' . $e->getMessage());
            throw new \Exception('Google authentication service is currently unavailable');
        }
    }

    public function redirectToGoogle()
    {
        try {
            // Generate and store state parameter for CSRF protection
            $state = bin2hex(random_bytes(16));
            $this->session->set('google_auth_state', $state);
            
            $this->client->setState($state);
            $authUrl = $this->client->createAuthUrl();
            
            header('Location: ' . $authUrl);
            exit;
        } catch (\Exception $e) {
            $this->logger->error('Google redirect failed: ' . $e->getMessage());
            $this->session->set('error', 'Failed to initiate Google login. Please try again.');
            header('Location: /login');
            exit;
        }
    }

    public function handleCallback()
    {
        try {
            // Verify state parameter
            $storedState = $this->session->get('google_auth_state');
            $receivedState = $_GET['state'] ?? '';
            
            if (!$storedState || !$receivedState || $storedState !== $receivedState) {
                throw new \Exception('Invalid state parameter');
            }
            
            // Clear the state parameter
            $this->session->remove('google_auth_state');

            if (!isset($_GET['code'])) {
                throw new \Exception('Authorization code not received');
            }

            // Exchange authorization code for access token
            $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
            
            if (isset($token['error'])) {
                throw new \Exception('Token error: ' . $token['error']);
            }

            $this->client->setAccessToken($token);

            // Get user info from Google
            $oauth2 = new Google_Service_Oauth2($this->client);
            $userInfo = $oauth2->userinfo->get();

            // Prepare user data
            $userData = [
                'email' => $userInfo->getEmail(),
                'name' => $userInfo->getName(),
                'google_id' => $userInfo->getId(),
                'avatar' => $userInfo->getPicture(),
                'last_login' => date('Y-m-d H:i:s')
            ];

            // Check if user exists
            $existingUser = $this->userModel->findByEmail($userData['email']);
            
            if ($existingUser) {
                // Update existing user
                $this->userModel->update($existingUser['id'], [
                    'google_id' => $userData['google_id'],
                    'avatar' => $userData['avatar'],
                    'last_login' => $userData['last_login']
                ]);
                $userId = $existingUser['id'];
            } else {
                // Create new user
                $userId = $this->userModel->createUser($this->db, [
                    'email' => $userData['email'],
                    'name' => $userData['name'],
                    'google_id' => $userData['google_id'],
                    'avatar' => $userData['avatar'],
                    'password' => bin2hex(random_bytes(16)), // Random password for Google users
                    'status' => 'active'
                ]);
            }

            // Set session
            $this->session->set('user_id', $userId);
            $this->session->set('user_email', $userData['email']);
            $this->session->set('user_name', $userData['name']);
            $this->session->set('user_avatar', $userData['avatar']);
            $this->session->set('is_google_user', true);

            // Redirect to resources
            header('Location: /resources');
            exit;

        } catch (\Exception $e) {
            $this->logger->error('Google callback failed: ' . $e->getMessage());
            $this->session->set('error', 'Google login failed. Please try again.');
            header('Location: /login');
            exit;
        }
    }
} 