<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Comment;
use App\Models\Resource;
use App\Core\Auth;
use PDO;
use App\Core\Database;

class CommentController extends Controller
{
    protected $commentModel;
    protected $resourceModel;
    protected $auth;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        
        // 데이터베이스 연결 가져오기
        $this->db = Database::getInstance()->getConnection();
        
        // 모델 초기화
        $this->commentModel = new Comment($this->db);
        $this->resourceModel = new Resource($this->db);
        $this->auth = new Auth();
    }

    protected function getDb(): PDO
    {
        return $this->db;
    }

    public function index(Request $request, $resourceId)
    {
        try {
            $page = $request->get('page', 1);
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $comments = $this->commentModel->getByResourceId($resourceId, $limit, $offset);
            $total = $this->commentModel->countByResourceId($resourceId);

            // 각 댓글의 답글을 재귀적으로 가져오기
            foreach ($comments as &$comment) {
                $comment['replies'] = $this->getNestedReplies($comment['id']);
            }

            return $this->response->json([
                'success' => true,
                'data' => [
                    'comments' => $comments,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error in CommentController::index: " . $e->getMessage());
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 중첩 답글을 재귀적으로 가져오는 메서드
    private function getNestedReplies($commentId)
    {
        $replies = $this->commentModel->getReplies($commentId);
        foreach ($replies as &$reply) {
            $reply['replies'] = $this->getNestedReplies($reply['id']);
        }
        return $replies;
    }

    public function store(Request $request, $resourceId)
    {
        try {
            if (!$this->auth->check()) {
                throw new \Exception('로그인이 필요합니다.', 401);
            }

            // 요청 데이터 처리 (form-data 또는 JSON)
            $content = null;
            $parentId = null;

            // Content-Type 헤더 확인
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false) {
                // JSON 요청 처리
                $jsonData = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('잘못된 JSON 형식입니다.', 400);
                }
                $content = $jsonData['content'] ?? null;
                $parentId = $jsonData['parent_id'] ?? null;
            } else {
                // form-data 요청 처리
                $content = $request->get('content');
                $parentId = $request->get('parent_id');
            }

            if (empty($content)) {
                throw new \Exception('댓글 내용을 입력해주세요.', 400);
            }

            // 리소스 존재 확인
            $resource = $this->resourceModel->find($resourceId);
            if (!$resource) {
                throw new \Exception('존재하지 않는 리소스입니다.', 404);
            }

            $data = [
                'resource_id' => $resourceId,
                'user_id' => $this->auth->id(),
                'parent_id' => $parentId,
                'content' => $content
            ];

            $commentId = $this->commentModel->create($data);
            if (!$commentId) {
                throw new \Exception('댓글 등록에 실패했습니다.', 500);
            }

            $comment = $this->commentModel->find($commentId);
            if (!$comment) {
                throw new \Exception('등록된 댓글을 찾을 수 없습니다.', 500);
            }

            return $this->response->json([
                'success' => true,
                'message' => '댓글이 등록되었습니다.',
                'data' => $comment
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            if ($statusCode < 400 || $statusCode > 599) {
                $statusCode = 500;
            }
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function update(Request $request, $commentId)
    {
        try {
            if (!$this->auth->check()) {
                throw new \Exception('로그인이 필요합니다.', 401);
            }

            // JSON 요청 처리
            $jsonData = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('잘못된 JSON 형식입니다.', 400);
            }

            $content = $jsonData['content'] ?? null;
            if (empty($content)) {
                throw new \Exception('댓글 내용을 입력해주세요.', 400);
            }

            $comment = $this->commentModel->find($commentId);
            if (!$comment) {
                throw new \Exception('존재하지 않는 댓글입니다.', 404);
            }

            // 권한 확인 (작성자 또는 관리자만 수정 가능)
            if ($comment['user_id'] !== $this->auth->id() && !$this->auth->isAdmin()) {
                throw new \Exception('댓글을 수정할 권한이 없습니다.', 403);
            }

            $updated = $this->commentModel->update($commentId, ['content' => $content]);
            if (!$updated) {
                throw new \Exception('댓글 수정에 실패했습니다.', 500);
            }

            $comment = $this->commentModel->find($commentId);
            return $this->response->json([
                'success' => true,
                'message' => '댓글이 수정되었습니다.',
                'data' => $comment
            ]);
        } catch (\Exception $e) {
            error_log("Comment update error: " . $e->getMessage());
            $statusCode = $e->getCode() ?: 500;
            if ($statusCode < 400 || $statusCode > 599) {
                $statusCode = 500;
            }
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function destroy(Request $request, $commentId)
    {
        try {
            if (!$this->auth->check()) {
                throw new \Exception('로그인이 필요합니다.', 401);
            }

            $comment = $this->commentModel->find($commentId);
            if (!$comment) {
                error_log("Comment not found for deletion: ID = {$commentId}");
                throw new \Exception('존재하지 않는 댓글입니다.', 404);
            }

            // 권한 확인 (작성자 또는 관리자만 삭제 가능)
            if ($comment['user_id'] !== $this->auth->id() && !$this->auth->isAdmin()) {
                error_log("Unauthorized deletion attempt: Comment ID = {$commentId}, User ID = {$this->auth->id()}");
                throw new \Exception('댓글을 삭제할 권한이 없습니다.', 403);
            }

            $deleted = $this->commentModel->delete($commentId);
            if (!$deleted) {
                error_log("Failed to delete comment: ID = {$commentId}");
                throw new \Exception('댓글 삭제에 실패했습니다.', 500);
            }

            error_log("Comment successfully deleted: ID = {$commentId}");
            return $this->response->json([
                'success' => true,
                'message' => '댓글이 삭제되었습니다.'
            ]);
        } catch (\Exception $e) {
            error_log("Comment deletion error in controller: " . $e->getMessage());
            $statusCode = $e->getCode() ?: 500;
            if ($statusCode < 400 || $statusCode > 599) {
                $statusCode = 500;
            }
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function report(Request $request, $commentId)
    {
        try {
            if (!$this->auth->check()) {
                throw new \Exception('로그인이 필요합니다.', 401);
            }

            $reason = $request->get('reason');
            if (empty($reason)) {
                throw new \Exception('신고 사유를 입력해주세요.');
            }

            $comment = $this->commentModel->find($commentId);
            if (!$comment) {
                throw new \Exception('존재하지 않는 댓글입니다.');
            }

            $reported = $this->commentModel->report($commentId, $this->auth->id(), $reason);
            if (!$reported) {
                throw new \Exception('댓글 신고에 실패했습니다.');
            }

            return $this->response->json([
                'success' => true,
                'message' => '댓글이 신고되었습니다.'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function block(Request $request, $commentId)
    {
        try {
            if (!$this->auth->isAdmin()) {
                throw new \Exception('권한이 없습니다.', 403);
            }

            $comment = $this->commentModel->find($commentId);
            if (!$comment) {
                throw new \Exception('존재하지 않는 댓글입니다.');
            }

            $blocked = $this->commentModel->block($commentId);
            if (!$blocked) {
                throw new \Exception('댓글 차단에 실패했습니다.');
            }

            return $this->response->json([
                'success' => true,
                'message' => '댓글이 차단되었습니다.'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function addReaction(Request $request, $commentId)
    {
        try {
            if (!$this->auth->check()) {
                throw new \Exception('로그인이 필요합니다.', 401);
            }

            $reactionType = $request->get('reaction_type');
            if (!in_array($reactionType, ['like', 'dislike'])) {
                throw new \Exception('잘못된 반응 타입입니다.');
            }

            $comment = $this->commentModel->find($commentId);
            if (!$comment) {
                throw new \Exception('존재하지 않는 댓글입니다.');
            }

            $added = $this->commentModel->addReaction($commentId, $this->auth->id(), $reactionType);
            if (!$added) {
                throw new \Exception('반응 등록에 실패했습니다.');
            }

            return $this->response->json([
                'success' => true,
                'message' => '반응이 등록되었습니다.'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function removeReaction(Request $request, $commentId)
    {
        try {
            if (!$this->auth->check()) {
                throw new \Exception('로그인이 필요합니다.', 401);
            }

            $comment = $this->commentModel->find($commentId);
            if (!$comment) {
                throw new \Exception('존재하지 않는 댓글입니다.');
            }

            $removed = $this->commentModel->removeReaction($commentId, $this->auth->id());
            if (!$removed) {
                throw new \Exception('반응 제거에 실패했습니다.');
            }

            return $this->response->json([
                'success' => true,
                'message' => '반응이 제거되었습니다.'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function translate(Request $request, $commentId)
    {
        try {
            if (!$this->auth->check()) {
                throw new \Exception('로그인이 필요합니다.', 401);
            }

            $comment = $this->commentModel->find($commentId);
            if (!$comment) {
                throw new \Exception('존재하지 않는 댓글입니다.', 404);
            }

            // Google Translate API를 사용하여 번역
            $translatedContent = $this->translateText($comment['content'], 'en');
            
            return $this->response->json([
                'success' => true,
                'data' => [
                    'original' => $comment['content'],
                    'translated' => $translatedContent
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Translation error: " . $e->getMessage());
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    private function translateText($text, $targetLang)
    {
        // Google Translate API 키 설정
        $apiKey = getenv('GOOGLE_TRANSLATE_API_KEY');
        if (!$apiKey) {
            throw new \Exception('번역 서비스가 설정되지 않았습니다.', 500);
        }

        $url = 'https://translation.googleapis.com/language/translate/v2';
        $data = [
            'q' => $text,
            'target' => $targetLang,
            'key' => $apiKey
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('번역 서비스 오류가 발생했습니다.', 500);
        }

        $result = json_decode($response, true);
        if (!isset($result['data']['translations'][0]['translatedText'])) {
            throw new \Exception('번역 결과를 가져올 수 없습니다.', 500);
        }

        return $result['data']['translations'][0]['translatedText'];
    }
} 