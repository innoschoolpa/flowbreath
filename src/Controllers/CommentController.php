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

            foreach ($comments as &$comment) {
                $comment['replies'] = $this->commentModel->getReplies($comment['id']);
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
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
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

            $content = $request->get('content');
            if (empty($content)) {
                throw new \Exception('댓글 내용을 입력해주세요.');
            }

            $comment = $this->commentModel->find($commentId);
            if (!$comment) {
                throw new \Exception('존재하지 않는 댓글입니다.');
            }

            // 권한 확인
            if ($comment['user_id'] !== $this->auth->id() && !$this->auth->isAdmin()) {
                throw new \Exception('댓글을 수정할 권한이 없습니다.', 403);
            }

            $updated = $this->commentModel->update($commentId, ['content' => $content]);
            if (!$updated) {
                throw new \Exception('댓글 수정에 실패했습니다.');
            }

            $comment = $this->commentModel->find($commentId);
            return $this->response->json([
                'success' => true,
                'message' => '댓글이 수정되었습니다.',
                'data' => $comment
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
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
                throw new \Exception('존재하지 않는 댓글입니다.', 404);
            }

            // 권한 확인 (작성자 또는 관리자만 삭제 가능)
            if ($comment['user_id'] !== $this->auth->id() && !$this->auth->isAdmin()) {
                throw new \Exception('댓글을 삭제할 권한이 없습니다.', 403);
            }

            $deleted = $this->commentModel->delete($commentId);
            if (!$deleted) {
                throw new \Exception('댓글 삭제에 실패했습니다.', 500);
            }

            return $this->response->json([
                'success' => true,
                'message' => '댓글이 삭제되었습니다.'
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
} 