<?php
// src/Controller/HomeController.php

// 네임스페이스 사용 시 (composer.json에 App\\: src/ 설정 필요)
// namespace App\Controller;
// use PDO;

// 필요 모델 사용 명시 (네임스페이스 사용 시)
// use App\Model\Resource; // Resource 모델이 있다고 가정

// 헬퍼 함수 로드 (public/index.php에서 로드했다면 생략 가능)
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../Model/Resource.php';
// Resource 모델 로드 (Composer 미사용 및 Resource 모델 생성 시)
// require_once __DIR__ . '/../Model/Resource.php';

/**
 * HomeController 클래스
 * 웹사이트의 홈페이지 및 기본 정적 페이지 관련 요청을 처리합니다.
 */
class HomeController {
    /**
     * PDO 데이터베이스 연결 객체
     * @var PDO
     */
    private $pdo;

    // Resource 모델 객체 (선택적 - 나중에 Resource 모델 생성 후 사용)
    // private $resourceModel;

    /**
     * 생성자
     * @param PDO $pdo 데이터베이스 연결 객체 주입
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        // Resource 모델이 있다면 여기서 인스턴스화
        // $this->resourceModel = new Resource($pdo);
    }

    /**
     * 홈페이지 (index) 액션
     * 홈페이지에 필요한 데이터를 준비하고 뷰를 로드합니다.
     */
    public function index() {
        // 홈페이지 제목 설정
        $page_title = "FlowBreath.io에 오신 것을 환영합니다";

        // 홈페이지에 보여줄 최근 리소스 데이터 가져오기 (예: 최신 5개)
        $recentResources = [];
        try {
            // TODO: 나중에 Resource 모델을 만들고 해당 모델의 메소드를 호출하는 방식으로 변경하는 것이 좋습니다.
            // 예: $recentResources = $this->resourceModel->findRecent(5);
            $stmt = $this->pdo->query("SELECT * FROM resources WHERE is_public = 1 ORDER BY is_pinned DESC, date_added DESC LIMIT 6");
            $recentResources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching recent resources: " . $e->getMessage());
            $recentResources = [];
        }

        // 뷰에 전달할 데이터 준비
        $data = [
            'page_title' => $page_title,
            'recentResources' => $recentResources
            // 필요시 위에서 설정한 $data['db_error'] 등 추가
        ];

        // 홈페이지 뷰 파일 로드 및 데이터 전달
        // helpers.php에 정의된 load_view 함수 사용 가정
        try {
            // src/View/home/index.php 파일을 로드하고 $data 배열의 키를 변수로 사용할 수 있게 함
            load_view('home/index', $data);
        } catch (Exception $e) {
             // 뷰 파일을 찾을 수 없거나 로드 중 오류 발생 시
             error_log("Error loading home/index view: " . $e->getMessage());
             // 500 오류 페이지를 보여주는 것이 좋습니다.
             http_response_code(500);
             load_view('error/500'); // error/500.php 뷰 로드
        }
    }

    /**
     * 사이트 소개 페이지 액션 (예시)
     * '/about' 경로 요청 시 호출될 수 있습니다.
     */
    public function about() {
        $page_title = "FlowBreath.io 소개";
        $data = ['page_title' => $page_title];

        try {
            // src/View/static/about.php 뷰 파일을 로드합니다.
            load_view('static/about', $data);
        } catch (Exception $e) {
             error_log("Error loading static/about view: " . $e->getMessage());
             http_response_code(500);
             load_view('error/500');
        }
    }

    /**
     * 대시보드 페이지 액션
     * '/dashboard' 경로 요청 시 호출됩니다.
     */
    public function dashboard() {
        // 로그인 체크
        if (!is_logged_in()) {
            redirect('/login');
        }
        $page_title = "대시보드";
        
        // 리소스 모델 초기화
        $resourceModel = new Resource($this->pdo);
        
        // 전체 리소스 개수 가져오기
        $all_resources = $resourceModel->findAll();
        $resource_count = is_array($all_resources) ? count($all_resources) : 0;
        
        // 최근 리소스 가져오기 (최근 5개)
        $recent_resources = [];
        try {
            $stmt = $this->pdo->query("SELECT * FROM resources ORDER BY date_added DESC LIMIT 5");
            $recent_resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching recent resources: " . $e->getMessage());
            $recent_resources = [];
        }
        
        load_view('dashboard/index', [
            'page_title' => $page_title,
            'resource_count' => $resource_count,
            'recent_resources' => $recent_resources
        ]);
    }

    // --- 필요한 다른 정적 페이지 액션(메소드)들을 여기에 추가 ---
    // 예: 연락처(Contact), 서비스 안내(Service) 등
    // public function contact() {
    //     $page_title = "문의하기";
    //     $data = ['page_title' => $page_title];
    //     load_view('static/contact', $data); // src/View/static/contact.php 필요
    // }

    private function generateUniqueUsername($base)
    {
        // Remove spaces and special characters, make lowercase
        $username = preg_replace('/[^a-z0-9]/', '', strtolower($base));
        $originalUsername = $username;
        $i = 1;

        // Check if username exists in the database
        while (User::findByUsername($username)) {
            $username = $originalUsername . $i;
            $i++;
        }
        return $username;
    }
}

?>