<?php
namespace App\Models;

use PDO;
use PDOException;
use Exception;
use App\Core\Database;
use App\Core\Model;
use App\Exceptions\ModelException;
use App\Core\Language;

error_log("Resource.php loaded from: " . __FILE__); // 파일 로드 확인 로그

/**
 * 리소스 모델 클래스
 */
class Resource extends Model {
    protected $table = 'resources';
    protected $fillable = [
        'user_id', 'title', 'content', 'summary', 'url', 
        'visibility', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function __construct() {
        parent::__construct(Database::getInstance());
    }

    public function getResourcesWithTags($limit = null, $offset = null) {
        try {
            $sql = "SELECT r.*, GROUP_CONCAT(t.name) as tags 
                    FROM {$this->table} r 
                    LEFT JOIN resource_tags rt ON r.id = rt.resource_id 
                    LEFT JOIN tags t ON rt.tag_id = t.id 
                    GROUP BY r.id 
                    ORDER BY r.created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT ?";
                if ($offset !== null) {
                    $sql .= " OFFSET ?";
                }
            }

            $params = [];
            if ($limit !== null) {
                $params[] = $limit;
                if ($offset !== null) {
                    $params[] = $offset;
                }
            }

            $resources = $this->db->fetchAll($sql, $params);

            // 태그 문자열을 배열로 변환
            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }

            return $resources;
        } catch (PDOException $e) {
            error_log("Database error in getResourcesWithTags: " . $e->getMessage());
            throw new Exception("리소스 목록을 조회하는 중 오류가 발생했습니다.");
        }
    }

    public function getResourceWithTags($id) {
        try {
            $sql = "SELECT r.*, GROUP_CONCAT(t.name) as tags 
                    FROM {$this->table} r 
                    LEFT JOIN resource_tags rt ON r.id = rt.resource_id 
                    LEFT JOIN tags t ON rt.tag_id = t.id 
                    WHERE r.id = ? 
                    GROUP BY r.id";

            $resource = $this->db->fetch($sql, [$id]);

            if ($resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }

            return $resource;
        } catch (PDOException $e) {
            error_log("Database error in getResourceWithTags: " . $e->getMessage());
            throw new Exception("리소스 상세 정보를 조회하는 중 오류가 발생했습니다.");
        }
    }

    public function getRelatedResources($resource_id, $limit = 3) {
        try {
            $sql = "SELECT r.*, GROUP_CONCAT(t.name) as tags 
                    FROM {$this->table} r 
                    LEFT JOIN resource_tags rt ON r.id = rt.resource_id 
                    LEFT JOIN tags t ON rt.tag_id = t.id 
                    WHERE r.id != ? 
                    AND r.id IN (
                        SELECT r2.id 
                        FROM {$this->table} r2 
                        JOIN resource_tags rt2 ON r2.id = rt2.resource_id 
                        WHERE rt2.tag_id IN (
                            SELECT tag_id 
                            FROM resource_tags 
                            WHERE resource_id = ?
                        )
                    )
                    GROUP BY r.id 
                    ORDER BY r.created_at DESC 
                    LIMIT ?";

            $resources = $this->db->fetchAll($sql, [$resource_id, $resource_id, $limit]);

            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }

            return $resources;
        } catch (PDOException $e) {
            error_log("Database error in getRelatedResources: " . $e->getMessage());
            throw new Exception("관련 리소스를 조회하는 중 오류가 발생했습니다.");
        }
    }

    public function searchResources($query, $limit = null, $offset = null) {
        try {
            $sql = "SELECT r.*, GROUP_CONCAT(t.name) as tags 
                    FROM {$this->table} r 
                    LEFT JOIN resource_tags rt ON r.id = rt.resource_id 
                    LEFT JOIN tags t ON rt.tag_id = t.id 
                    WHERE r.title LIKE ? 
                    OR r.content LIKE ? 
                    GROUP BY r.id 
                    ORDER BY r.created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT ?";
                if ($offset !== null) {
                    $sql .= " OFFSET ?";
                }
            }

            $searchTerm = '%' . $query . '%';
            $params = [$searchTerm, $searchTerm];
            
            if ($limit !== null) {
                $params[] = $limit;
                if ($offset !== null) {
                    $params[] = $offset;
                }
            }

            $resources = $this->db->fetchAll($sql, $params);

            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }

            return $resources;
        } catch (PDOException $e) {
            error_log("Database error in searchResources: " . $e->getMessage());
            throw new Exception("리소스 검색 중 오류가 발생했습니다: " . $e->getMessage());
        }
    }

    /**
     * 리소스 검색
     */
    public function search($params = []) {
        try {
            $where_conditions = [];
            $having_conditions = [];
            $query_params = [];

            // 키워드 검색
            if (!empty($params['keyword'])) {
                $search_fields = $params['search_fields'] ?? ['title', 'content', 'summary'];
                $field_conditions = [];
                foreach ($search_fields as $field) {
                    $field_conditions[] = "r.$field LIKE ?";
                    $query_params[] = '%' . $params['keyword'] . '%';
                }
                $where_conditions[] = '(' . implode(' OR ', $field_conditions) . ')';
            }

            // 태그 검색
            if (!empty($params['tag_ids'])) {
                $placeholders = str_repeat('?,', count($params['tag_ids']) - 1) . '?';
                $where_conditions[] = "r.id IN (
                    SELECT resource_id 
                    FROM resource_tags 
                    WHERE tag_id IN ($placeholders)
                    GROUP BY resource_id 
                    HAVING COUNT(DISTINCT tag_id) = ?
                )";
                $query_params = array_merge($query_params, $params['tag_ids'], [count($params['tag_ids'])]);
            }

            // 날짜 범위 검색
            if (!empty($params['date_from'])) {
                $where_conditions[] = "r.created_at >= ?";
                $query_params[] = $params['date_from'];
            }
            if (!empty($params['date_to'])) {
                $where_conditions[] = "r.created_at <= ?";
                $query_params[] = $params['date_to'] . ' 23:59:59';
            }

            // 공개 여부 필터
            if (isset($params['is_public'])) {
                $where_conditions[] = "r.is_public = ?";
                $query_params[] = $params['is_public'];
            }

            // 리소스 유형 필터
            if (!empty($params['source_type'])) {
                $where_conditions[] = "r.source_type = ?";
                $query_params[] = $params['source_type'];
            }

            // 최소 평점 필터
            if (!empty($params['min_rating'])) {
                $having_conditions[] = "COALESCE(rating_avg, 0) >= ?";
                $query_params[] = $params['min_rating'];
            }

            // 최소 조회수 필터
            if (!empty($params['min_views'])) {
                $having_conditions[] = "COALESCE(view_count, 0) >= ?";
                $query_params[] = $params['min_views'];
            }

            // WHERE 절 구성
            $where_clause = !empty($where_conditions) 
                ? 'WHERE ' . implode(' AND ', $where_conditions) 
                : '';

            // HAVING 절 구성
            $having_clause = !empty($having_conditions)
                ? 'HAVING ' . implode(' AND ', $having_conditions)
                : '';

            // 정렬 조건
            $order_by = !empty($params['order_by']) 
                ? $params['order_by'] 
                : 'r.created_at DESC';

            // 페이지네이션
            $limit_clause = '';
            if (!empty($params['limit'])) {
                $limit_clause = ' LIMIT ?';
                $query_params[] = (int)$params['limit'];
                
                if (!empty($params['offset'])) {
                    $limit_clause .= ' OFFSET ?';
                    $query_params[] = (int)$params['offset'];
                }
            }

            // 최종 쿼리 구성
            $sql = "SELECT r.*, 
                    GROUP_CONCAT(DISTINCT t.name) as tags,
                    COUNT(DISTINCT rv.id) as view_count,
                    AVG(rr.rating) as rating_avg,
                    COUNT(DISTINCT rr.id) as rating_count
                    FROM {$this->table} r
                    LEFT JOIN resource_tags rt ON r.id = rt.resource_id
                    LEFT JOIN tags t ON rt.tag_id = t.id
                    LEFT JOIN resource_views rv ON r.id = rv.resource_id
                    LEFT JOIN resource_ratings rr ON r.id = rr.resource_id
                    $where_clause
                    GROUP BY r.id
                    $having_clause
                    ORDER BY $order_by
                    $limit_clause";

            $resources = $this->db->fetchAll($sql, $query_params);

            // 태그 문자열을 배열로 변환
            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
                $resource['view_count'] = (int)$resource['view_count'];
                $resource['rating_avg'] = round((float)$resource['rating_avg'], 1);
                $resource['rating_count'] = (int)$resource['rating_count'];
            }

            return $resources;
        } catch (PDOException $e) {
            error_log("Database error in search: " . $e->getMessage());
            throw new Exception("리소스 검색 중 오류가 발생했습니다.");
        }
    }

    /**
     * 모든 태그 조회
     */
    public function getAllTags() {
        try {
            $sql = "SELECT DISTINCT t.* 
                    FROM tags t 
                    JOIN resource_tags rt ON t.id = rt.tag_id 
                    ORDER BY t.name";
            return $this->db->fetchAll($sql);
        } catch (PDOException $e) {
            error_log("Database error in getAllTags: " . $e->getMessage());
            throw new Exception("태그 목록을 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * ID로 리소스 조회 (태그 포함)
     */
    public function getById($id) {
        try {
            $sql = "SELECT r.*, u.username, u.display_name 
                    FROM {$this->table} r 
                    LEFT JOIN users u ON r.user_id = u.id 
                    WHERE r.id = ?";
            return $this->db->fetch($sql, [$id]);
        } catch (PDOException $e) {
            error_log("Database error in getById: " . $e->getMessage());
            throw new Exception("리소스를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스의 태그 조회
     */
    public function getTags($resource_id) {
        try {
            $sql = "SELECT t.* 
                    FROM tags t 
                    JOIN resource_tags rt ON t.id = rt.tag_id 
                    WHERE rt.resource_id = ?";
            return $this->db->fetchAll($sql, [$resource_id]);
        } catch (PDOException $e) {
            error_log("Database error in getTags: " . $e->getMessage());
            throw new Exception("리소스의 태그를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 태그 이름으로 태그 찾기
     */
    public function findTagByName($name) {
        try {
            $sql = "SELECT * FROM tags WHERE name = ?";
            return $this->db->fetch($sql, [$name]);
        } catch (PDOException $e) {
            error_log("Database error in findTagByName: " . $e->getMessage());
            throw new Exception("태그를 찾는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 새 태그 생성
     */
    public function createTag($name) {
        try {
            $sql = "INSERT INTO tags (name) VALUES (?)";
            $this->db->query($sql, [$name]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database error in createTag: " . $e->getMessage());
            throw new Exception("태그를 생성하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스에 태그 추가
     */
    public function addTag($resource_id, $tag_name) {
        try {
            $this->db->beginTransaction();

            // 태그가 없으면 생성
            $tag = $this->findTagByName($tag_name);
            if (!$tag) {
                $tag_id = $this->createTag($tag_name);
            } else {
                $tag_id = $tag['id'];
            }

            // 리소스에 태그 추가
            $sql = "INSERT IGNORE INTO resource_tags (resource_id, tag_id) VALUES (?, ?)";
            $this->db->query($sql, [$resource_id, $tag_id]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Database error in addTag: " . $e->getMessage());
            throw new Exception("태그를 추가하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스에 기존 태그 추가
     */
    public function addExistingTag($resource_id, $tag_id) {
        try {
            $sql = "INSERT IGNORE INTO resource_tags (resource_id, tag_id) VALUES (?, ?)";
            $this->db->query($sql, [$resource_id, $tag_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error in addExistingTag: " . $e->getMessage());
            throw new Exception("태그를 추가하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스에서 태그 제거
     */
    public function removeTag($resource_id, $tag_id) {
        try {
            $sql = "DELETE FROM resource_tags WHERE resource_id = ? AND tag_id = ?";
            $this->db->query($sql, [$resource_id, $tag_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error in removeTag: " . $e->getMessage());
            throw new Exception("태그를 제거하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 공개/비공개 상태 변경
     */
    public function updateVisibility($resource_id, $is_private) {
        try {
            $sql = "UPDATE {$this->table} SET is_private = ? WHERE id = ?";
            $this->db->query($sql, [$is_private, $resource_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error in updateVisibility: " . $e->getMessage());
            throw new Exception("리소스의 공개 여부를 업데이트하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 생성
     */
    public function create(array $data): ?array {
        try {
            $this->db->beginTransaction();

            // 기본 리소스 정보 저장
            $sql = "INSERT INTO {$this->table} (user_id, title, content, summary, url, is_private, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->query($sql, [
                $data['user_id'],
                $data['title'],
                $data['content'],
                $data['summary'] ?? null,
                $data['url'] ?? null,
                $data['is_private'] ?? false
            ]);

            $resource_id = $this->db->lastInsertId();

            // 태그 처리
            if (!empty($data['tags'])) {
                foreach ($data['tags'] as $tag) {
                    $this->addTag($resource_id, $tag);
                }
            }

            $this->db->commit();
            return $this->find($resource_id);
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error in create: " . $e->getMessage());
            throw new Exception("리소스를 생성하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 업데이트
     */
    public function update(int $id, array $data): ?array
    {
        try {
            $this->db->beginTransaction();

            // 기본 리소스 정보 업데이트
            $sql = "UPDATE {$this->table} 
                    SET title = ?, content = ?, summary = ?, url = ?, visibility = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $this->db->query($sql, [
                $data['title'],
                $data['content'],
                $data['summary'] ?? null,
                $data['url'] ?? null,
                $data['visibility'] ?? 'private',
                $id
            ]);

            // 태그 처리
            if (isset($data['tags'])) {
                $this->updateResourceTags($id, $data['tags']);
            }

            $this->db->commit();
            return $this->find($id);
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error in update: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 리소스 삭제
     */
    public function delete(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            // 태그 관계 삭제
            $sql = "DELETE FROM resource_tags WHERE resource_id = ?";
            $this->db->query($sql, [$id]);

            // 리소스 삭제
            $result = parent::delete($id);

            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error in delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 모든 리소스 조회
     */
    public function findAll() {
        try {
            $sql = "SELECT r.*, GROUP_CONCAT(t.name) as tags 
                    FROM {$this->table} r 
                    LEFT JOIN resource_tags rt ON r.id = rt.resource_id 
                    LEFT JOIN tags t ON rt.tag_id = t.id 
                    GROUP BY r.id 
                    ORDER BY r.created_at DESC";
            
            $resources = $this->db->fetchAll($sql);

            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }

            return $resources;
        } catch (PDOException $e) {
            error_log("Error in findAll: " . $e->getMessage());
            throw new Exception("리소스 목록을 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스의 태그 ID 목록 조회
     */
    public function getResourceTagIds($resource_id) {
        try {
            $sql = "SELECT tag_id FROM resource_tags WHERE resource_id = ?";
            return $this->db->fetchAll($sql, [$resource_id], PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error in getResourceTagIds: " . $e->getMessage());
            throw new Exception("리소스의 태그 ID를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스의 태그 업데이트
     */
    public function updateResourceTags($resource_id, array $tag_ids) {
        try {
            $this->db->beginTransaction();

            // 기존 태그 삭제
            $sql = "DELETE FROM resource_tags WHERE resource_id = ?";
            $this->db->query($sql, [$resource_id]);

            // 새로운 태그 추가
            if (!empty($tag_ids)) {
                $values = [];
                $params = [];
                foreach ($tag_ids as $tag_id) {
                    $values[] = "(?, ?)";
                    $params[] = $resource_id;
                    $params[] = $tag_id;
                }
                
                $sql = "INSERT INTO resource_tags (resource_id, tag_id) VALUES " . implode(', ', $values);
                $this->db->query($sql, $params);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error in updateResourceTags: " . $e->getMessage());
            throw new Exception("리소스의 태그를 업데이트하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 조회수 증가 및 조회 기록
     */
    public function recordView($resource_id, $viewer_id) {
        try {
            $this->db->beginTransaction();

            // 이미 조회한 기록이 있는지 확인
            $sql = "SELECT id FROM resource_views 
                    WHERE resource_id = ? AND viewer_id = ? 
                    AND DATE(viewed_at) = CURDATE()";
            $existing = $this->db->fetch($sql, [$resource_id, $viewer_id]);

            if (!$existing) {
                // 새로운 조회 기록 추가
                $sql = "INSERT INTO resource_views (resource_id, viewer_id, viewed_at) 
                        VALUES (?, ?, NOW())";
                $this->db->query($sql, [$resource_id, $viewer_id]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error in recordView: " . $e->getMessage());
            throw new Exception("조회수를 기록하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 평점 등록/수정
     */
    public function rateResource($resource_id, $viewer_id, $rating) {
        try {
            if ($rating < 1 || $rating > 5) {
                throw new Exception("평점은 1에서 5 사이여야 합니다.");
            }

            $this->db->beginTransaction();

            // 기존 평점 업데이트 또는 새로운 평점 추가
            $sql = "INSERT INTO resource_ratings (resource_id, user_id, rating, created_at) 
                    VALUES (?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE rating = ?";
            
            $this->db->query($sql, [$resource_id, $viewer_id, $rating, $rating]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error in rateResource: " . $e->getMessage());
            throw new Exception("평점을 등록하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스의 평균 평점 조회
     */
    public function getAverageRating($resource_id) {
        try {
            $sql = "SELECT AVG(rating) as avg_rating, COUNT(rating) as rating_count
                    FROM resource_ratings
                    WHERE resource_id = ? AND rating IS NOT NULL";
            
            $result = $this->db->fetch($sql, [$resource_id]);
            return [
                'average' => round($result['avg_rating'] ?? 0, 1),
                'count' => (int)($result['rating_count'] ?? 0)
            ];
        } catch (PDOException $e) {
            error_log("Error in getAverageRating: " . $e->getMessage());
            throw new Exception("평균 평점을 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스의 조회수 조회
     */
    public function getViewCount($resource_id) {
        try {
            $sql = "SELECT COUNT(DISTINCT viewer_id) as view_count
                    FROM resource_views
                    WHERE resource_id = ?";
            
            $result = $this->db->fetch($sql, [$resource_id]);
            return (int)($result['view_count'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error in getViewCount: " . $e->getMessage());
            throw new Exception("조회수를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 사용자의 리소스 평점 조회
     */
    public function getUserRating($resource_id, $user_id) {
        try {
            $sql = "SELECT rating
                    FROM resource_ratings
                    WHERE resource_id = ? AND user_id = ?";
            
            $result = $this->db->fetch($sql, [$resource_id, $user_id]);
            return $result ? (int)$result['rating'] : null;
        } catch (PDOException $e) {
            error_log("Error in getUserRating: " . $e->getMessage());
            throw new Exception("사용자의 평점을 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 북마크 추가
     */
    public function addBookmark($resource_id, $user_id, $folder_name = 'default', $note = '') {
        try {
            $sql = "INSERT INTO bookmarks (user_id, resource_id, folder_name, note, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE folder_name = ?, note = ?, updated_at = NOW()";
            
            $this->db->query($sql, [
                $user_id, $resource_id, $folder_name, $note,
                $folder_name, $note
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Error in addBookmark: " . $e->getMessage());
            throw new Exception("북마크를 추가하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 북마크 제거
     */
    public function removeBookmark($resource_id, $user_id) {
        try {
            $sql = "DELETE FROM bookmarks WHERE resource_id = ? AND user_id = ?";
            $this->db->query($sql, [$resource_id, $user_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Error in removeBookmark: " . $e->getMessage());
            throw new Exception("북마크를 제거하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 사용자의 북마크 폴더 목록 조회
     */
    public function getBookmarkFolders($user_id) {
        try {
            $sql = "SELECT DISTINCT folder_name, COUNT(*) as count
                    FROM bookmarks
                    WHERE user_id = ?
                    GROUP BY folder_name
                    ORDER BY folder_name";
            
            return $this->db->fetchAll($sql, [$user_id]);
        } catch (PDOException $e) {
            error_log("Error in getBookmarkFolders: " . $e->getMessage());
            throw new Exception("북마크 폴더를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 사용자의 북마크된 리소스 목록 조회
     */
    public function getBookmarkedResources($user_id, $folder_name = null) {
        try {
            $sql = "SELECT r.*, b.folder_name, b.note, b.created_at as bookmarked_at
                    FROM {$this->table} r
                    JOIN bookmarks b ON r.id = b.resource_id
                    WHERE b.user_id = ?";
            
            $params = [$user_id];
            
            if ($folder_name !== null) {
                $sql .= " AND b.folder_name = ?";
                $params[] = $folder_name;
            }
            
            $sql .= " ORDER BY b.created_at DESC";
            
            return $this->db->fetchAll($sql, $params);
        } catch (PDOException $e) {
            error_log("Error in getBookmarkedResources: " . $e->getMessage());
            throw new Exception("북마크된 리소스를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 공유 링크 생성
     */
    public function createShareLink($resource_id, $user_id, $expires_in = null) {
        try {
            $this->db->beginTransaction();

            // 기존 공유 링크 확인
            $sql = "SELECT share_token FROM resource_shares 
                    WHERE resource_id = ? AND user_id = ? 
                    AND (expires_at IS NULL OR expires_at > NOW())";
            $existing = $this->db->fetch($sql, [$resource_id, $user_id]);

            if ($existing) {
                $this->db->commit();
                return $existing['share_token'];
            }

            // 새로운 공유 링크 생성
            $share_token = bin2hex(random_bytes(16));
            $expires_at = $expires_in ? date('Y-m-d H:i:s', strtotime("+$expires_in")) : null;

            $sql = "INSERT INTO resource_shares (resource_id, user_id, share_token, expires_at) 
                    VALUES (?, ?, ?, ?)";
            
            $this->db->query($sql, [$resource_id, $user_id, $share_token, $expires_at]);

            $this->db->commit();
            return $share_token;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error in createShareLink: " . $e->getMessage());
            throw new Exception("공유 링크를 생성하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 공유 링크로 리소스 조회
     */
    public function getByShareToken($share_token) {
        try {
            $sql = "SELECT r.*, u.name as user_name, 
                    s.created_at as shared_at, s.expires_at,
                    GROUP_CONCAT(t.name) as tags
                    FROM {$this->table} r
                    JOIN resource_shares s ON r.id = s.resource_id
                    LEFT JOIN users u ON r.user_id = u.id
                    LEFT JOIN resource_tags rt ON r.id = rt.resource_id
                    LEFT JOIN tags t ON rt.tag_id = t.id
                    WHERE s.share_token = ?
                    AND (s.expires_at IS NULL OR s.expires_at > NOW())
                    GROUP BY r.id";

            $resource = $this->db->fetch($sql, [$share_token]);

            if ($resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }

            return $resource;
        } catch (PDOException $e) {
            error_log("Database error in getByShareToken: " . $e->getMessage());
            throw new Exception("공유된 리소스를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스가 북마크되었는지 확인
     */
    public function isBookmarked($resource_id, $user_id) {
        try {
            $sql = "SELECT COUNT(*) as count
                    FROM bookmarks
                    WHERE resource_id = ? AND user_id = ?";
            
            $result = $this->db->fetch($sql, [$resource_id, $user_id]);
            return (int)($result['count'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error in isBookmarked: " . $e->getMessage());
            throw new Exception("북마크 여부를 확인하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 최근 공개 리소스를 가져옵니다.
     *
     * @param int $limit 가져올 리소스의 수
     * @return array 리소스 목록 (태그 포함)
     * @throws ModelException
     */
    public function getRecentPublic($limit = 10)
    {
        try {
            $sql = "SELECT r.*, u.name as user_name 
                    FROM resources r
                    LEFT JOIN users u ON r.user_id = u.id
                    WHERE r.visibility = 'public'
                    ORDER BY r.created_at DESC
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$limit]);
        } catch (PDOException $e) {
            error_log("Database error in getRecentPublic: " . $e->getMessage());
            throw new Exception("최근 공개 리소스를 조회하는 중 오류가 발생했습니다.");
        }
    }

    public function searchWithLang($params) {
        try {
            $lang = Language::getInstance();
            $params['error_messages'] = [
                'db_error' => $lang->get('error.database'),
                'not_found' => $lang->get('error.resource_not_found'),
                'invalid_params' => $lang->get('error.invalid_parameters')
            ];
            return $this->search($params);
        } catch (Exception $e) {
            error_log("Error in searchWithLang: " . $e->getMessage());
            throw new Exception($params['error_messages']['db_error']);
        }
    }
}
