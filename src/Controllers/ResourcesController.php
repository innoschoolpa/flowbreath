<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Resource;
use App\Core\Auth;
use App\Core\Validator;

class ResourcesController extends Controller
{
    private $resource;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $db = \App\Core\Database::getInstance();
        $this->resource = new Resource($db);
    }

    // GET /resources/create
    public function create()
    {
        $auth = Auth::getInstance();
        if (!$auth->check()) {
            $_SESSION['error'] = '로그인이 필요합니다.';
            header('Location: /login');
            exit;
        }
        return $this->view('resources/create');
    }

    // POST /resources/store
    public function store()
    {
        $auth = Auth::getInstance();
        if (!$auth->check()) {
            return $this->json(['error' => '로그인이 필요합니다.'], 401);
        }
        $userId = $auth->id();
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $category = $_POST['category'] ?? '';
        $tags = $_POST['tags'] ?? '';
        $filePath = null;

        // 입력값 검증
        $validator = new Validator();
        $validator->validate([
            'title' => [
                'required' => true,
                'min' => 2,
                'max' => 100,
                'message' => '제목은 2~100자 사이로 입력해주세요.'
            ],
            'content' => [
                'required' => true,
                'min' => 5,
                'message' => '내용을 입력해주세요.'
            ]
        ]);

        // 파일 업로드 처리
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!in_array($_FILES['file']['type'], $allowedTypes)) {
                return $this->json(['error' => '지원하지 않는 파일 형식입니다.'], 400);
            }
            $uploadDir = __DIR__ . '/../../public/uploads/resources/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
            $targetPath = $uploadDir . $fileName;
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                return $this->json(['error' => '파일 업로드에 실패했습니다.'], 500);
            }
            $filePath = '/uploads/resources/' . $fileName;
        }

        // DB 저장
        $data = [
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'category' => $category,
            'tags' => $tags,
            'file_path' => $filePath,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $resourceId = $this->resource->create($data);
        if (!$resourceId) {
            return $this->json(['error' => '리소스 등록에 실패했습니다.'], 500);
        }
        return $this->json(['success' => true, 'redirect' => '/resources/' . $resourceId]);
    }
} 