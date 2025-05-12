<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Comment;
use App\Models\Resource;

class CommentController extends Controller
{
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
} 