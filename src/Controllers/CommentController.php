<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Comment;
use App\Models\Resource;
use App\Core\Auth;

class CommentController extends Controller
{
    private $commentModel;
    private $request;
    private $response;

    public function __construct() {
        $this->commentModel = new Comment();
        $this->request = new Request();
        $this->response = new Response();
    }

    public function index(Request $request, $resourceId)
    {
        try {
            $page = $request->getQuery('page', 1);
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $comments = Comment::getResourceComments($resourceId, $limit, $offset);
            $total = Comment::countResourceComments($resourceId);

            foreach ($comments as &$comment) {
                $comment['replies'] = Comment::getReplies($comment['id']);
            }

            return Response::json([
                'success' => true,
                'data' => [
                    'comments' => $comments,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit
                ]
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, $resourceId)
    {
        try {
            $userId = $request->getUser()->id;
            $content = $request->getPost('content');
            $parentId = $request->getPost('parent_id');

            if (empty($content)) {
                throw new \Exception('댓글 내용을 입력해주세요.');
            }

            // 리소스 존재 확인
            $resource = Resource::findOrFail($resourceId);

            $comment = new Comment([
                'resource_id' => $resourceId,
                'user_id' => $userId,
                'parent_id' => $parentId,
                'content' => $content
            ]);

            if ($comment->save()) {
                // 저장된 댓글 정보 조회
                $commentData = Comment::getResourceComments($resourceId, 1, 0)[0] ?? null;

                return Response::json([
                    'success' => true,
                    'message' => '댓글이 등록되었습니다.',
                    'data' => $commentData
                ]);
            }

            throw new \Exception('댓글 등록에 실패했습니다.');
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $commentId)
    {
        try {
            $userId = $request->getUser()->id;
            $content = $request->getPost('content');

            if (empty($content)) {
                throw new \Exception('댓글 내용을 입력해주세요.');
            }

            $comment = Comment::findOrFail($commentId);

            // 권한 확인
            if ($comment->user_id !== $userId) {
                throw new \Exception('댓글을 수정할 권한이 없습니다.');
            }

            $comment->content = $content;
            
            if ($comment->save()) {
                return Response::json([
                    'success' => true,
                    'message' => '댓글이 수정되었습니다.',
                    'data' => $comment
                ]);
            }

            throw new \Exception('댓글 수정에 실패했습니다.');
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $commentId)
    {
        try {
            $userId = $request->getUser()->id;
            $comment = Comment::findOrFail($commentId);

            // 권한 확인
            if ($comment->user_id !== $userId) {
                throw new \Exception('댓글을 삭제할 권한이 없습니다.');
            }

            if ($comment->softDelete()) {
                return Response::json([
                    'success' => true,
                    'message' => '댓글이 삭제되었습니다.'
                ]);
            }

            throw new \Exception('댓글 삭제에 실패했습니다.');
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function create() {
        if (!Auth::check()) {
            return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $data = $this->request->getJson();
        
        // 입력값 검증
        if (empty($data['content'])) {
            return $this->response->json(['error' => '댓글 내용을 입력해주세요.'], 400);
        }

        if (mb_strlen($data['content']) > 1000) {
            return $this->response->json(['error' => '댓글은 1000자를 초과할 수 없습니다.'], 400);
        }

        // HTML 태그 제거
        $data['content'] = strip_tags($data['content']);
        
        // 부모 댓글이 있는 경우 깊이 계산
        if (!empty($data['parent_id'])) {
            $parentComment = $this->commentModel->getById($data['parent_id']);
            if (!$parentComment) {
                return $this->response->json(['error' => '부모 댓글을 찾을 수 없습니다.'], 404);
            }
            
            if ($parentComment['depth'] >= 5) {
                return $this->response->json(['error' => '더 이상 답글을 달 수 없습니다.'], 400);
            }
            
            $data['depth'] = $parentComment['depth'] + 1;
        }

        $data['user_id'] = Auth::id();
        
        if ($this->commentModel->create($data)) {
            return $this->response->json(['message' => '댓글이 작성되었습니다.']);
        }

        return $this->response->json(['error' => '댓글 작성에 실패했습니다.'], 500);
    }

    public function update($id) {
        if (!Auth::check()) {
            return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $comment = $this->commentModel->getById($id);
        if (!$comment) {
            return $this->response->json(['error' => '댓글을 찾을 수 없습니다.'], 404);
        }

        // 권한 체크
        if ($comment['user_id'] !== Auth::id() && !Auth::isAdmin()) {
            return $this->response->json(['error' => '권한이 없습니다.'], 403);
        }

        $data = $this->request->getJson();
        
        if (empty($data['content'])) {
            return $this->response->json(['error' => '댓글 내용을 입력해주세요.'], 400);
        }

        if (mb_strlen($data['content']) > 1000) {
            return $this->response->json(['error' => '댓글은 1000자를 초과할 수 없습니다.'], 400);
        }

        // HTML 태그 제거
        $data['content'] = strip_tags($data['content']);

        if ($this->commentModel->update($id, $data)) {
            return $this->response->json(['message' => '댓글이 수정되었습니다.']);
        }

        return $this->response->json(['error' => '댓글 수정에 실패했습니다.'], 500);
    }

    public function delete($id) {
        if (!Auth::check()) {
            return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $comment = $this->commentModel->getById($id);
        if (!$comment) {
            return $this->response->json(['error' => '댓글을 찾을 수 없습니다.'], 404);
        }

        // 권한 체크
        if ($comment['user_id'] !== Auth::id() && !Auth::isAdmin()) {
            return $this->response->json(['error' => '권한이 없습니다.'], 403);
        }

        if ($this->commentModel->delete($id)) {
            return $this->response->json(['message' => '댓글이 삭제되었습니다.']);
        }

        return $this->response->json(['error' => '댓글 삭제에 실패했습니다.'], 500);
    }

    public function getByResource($resourceId) {
        $page = $this->request->get('page', 1);
        $comments = $this->commentModel->getByResourceId($resourceId, $page);
        
        return $this->response->json([
            'comments' => $comments,
            'page' => $page
        ]);
    }

    public function getReplies($parentId) {
        $replies = $this->commentModel->getReplies($parentId);
        return $this->response->json(['replies' => $replies]);
    }

    public function report($id) {
        if (!Auth::check()) {
            return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $data = $this->request->getJson();
        if (empty($data['reason'])) {
            return $this->response->json(['error' => '신고 사유를 입력해주세요.'], 400);
        }

        if ($this->commentModel->report($id, Auth::id(), $data['reason'])) {
            return $this->response->json(['message' => '댓글이 신고되었습니다.']);
        }

        return $this->response->json(['error' => '댓글 신고에 실패했습니다.'], 500);
    }

    public function block($id) {
        if (!Auth::isAdmin()) {
            return $this->response->json(['error' => '권한이 없습니다.'], 403);
        }

        if ($this->commentModel->block($id)) {
            return $this->response->json(['message' => '댓글이 차단되었습니다.']);
        }

        return $this->response->json(['error' => '댓글 차단에 실패했습니다.'], 500);
    }

    public function addReaction($id) {
        if (!Auth::check()) {
            return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
        }

        $data = $this->request->getJson();
        if (!in_array($data['reaction_type'], ['like', 'dislike'])) {
            return $this->response->json(['error' => '잘못된 반응 타입입니다.'], 400);
        }

        if ($this->commentModel->addReaction($id, Auth::id(), $data['reaction_type'])) {
            return $this->response->json(['message' => '반응이 추가되었습니다.']);
        }

        return $this->response->json(['error' => '반응 추가에 실패했습니다.'], 500);
    }

    public function removeReaction($id) {
        if (!Auth::check()) {
            return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
        }

        if ($this->commentModel->removeReaction($id, Auth::id())) {
            return $this->response->json(['message' => '반응이 제거되었습니다.']);
        }

        return $this->response->json(['error' => '반응 제거에 실패했습니다.'], 500);
    }
} 