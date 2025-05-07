<?php
namespace App\Models;

use PDO;
use PDOException;
use Exception;
use App\Core\Database;
use App\Models\BaseModel;
use App\Exceptions\ModelException;

error_log("Resource.php loaded from: " . __FILE__); // 파일 로드 확인 로그

/**
 * 리소스 모델 클래스
 */
class Resource extends BaseModel {
    protected string $table = 'resources';
    protected array $fillable = [
        'user_id', 'title', 'content', 'summary', 'url', 
        'is_private', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
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
                $sql .= " LIMIT :limit";
                if ($offset !== null) {
                    $sql .= " OFFSET :offset";
                }
            }

            $stmt = $this->pdo->prepare($sql);
            
            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                }
            }

            $stmt->execute();
            $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                    WHERE r.id = :id 
                    GROUP BY r.id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $resource = $stmt->fetch(PDO::FETCH_ASSOC);

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
                    WHERE r.id != :resource_id 
                    AND r.id IN (
                        SELECT r2.id 
                        FROM {$this->table} r2 
                        JOIN resource_tags rt2 ON r2.id = rt2.resource_id 
                        WHERE rt2.tag_id IN (
                            SELECT tag_id 
                            FROM resource_tags 
                            WHERE resource_id = :resource_id
                        )
                    )
                    GROUP BY r.id 
                    ORDER BY r.created_at DESC 
                    LIMIT :limit";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':resource_id', $resource_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                    WHERE r.title LIKE :query 
                    OR r.content LIKE :query 
                    OR r.summary LIKE :query 
                    GROUP BY r.id 
                    ORDER BY r.created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT :limit";
                if ($offset !== null) {
                    $sql .= " OFFSET :offset";
                }
            }

            $stmt = $this->pdo->prepare($sql);
            $searchQuery = "%{$query}%";
            $stmt->bindValue(':query', $searchQuery, PDO::PARAM_STR);

            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                }
            }

            $stmt->execute();
            $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }

            return $resources;
        } catch (PDOException $e) {
            error_log("Database error in searchResources: " . $e->getMessage());
            throw new Exception("리소스 검색 중 오류가 발생했습니다.");
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

            // 정렬 조건 설정
            $order_clause = match($params['sort'] ?? 'created_desc') {
                'created_asc' => 'r.created_at ASC',
                'title_asc' => 'r.title ASC',
                'views_desc' => 'view_count DESC, r.created_at DESC',
                'rating_desc' => 'rating_avg DESC, r.created_at DESC',
                'relevance' => !empty($params['keyword']) 
                    ? 'MATCH(r.title, r.content, r.summary) AGAINST (? IN BOOLEAN MODE) DESC'
                    : 'r.created_at DESC',
                default => 'r.created_at DESC'
            };

            // 관련도 검색을 위한 파라미터 추가
            if (($params['sort'] ?? '') === 'relevance' && !empty($params['keyword'])) {
                $query_params[] = $params['keyword'];
            }

            // 쿼리 실행
            $sql = "
                SELECT r.*, 
                       GROUP_CONCAT(DISTINCT t.name) as tag_names,
                       GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                       u.name as author_name,
                       COALESCE(rv.rating_avg, 0) as rating,
                       COALESCE(rv.view_count, 0) as views
                FROM resources r
                LEFT JOIN resource_tags rt ON r.id = rt.resource_id
                LEFT JOIN tags t ON rt.tag_id = t.id
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN (
                    SELECT resource_id,
                           AVG(rating) as rating_avg,
                           COUNT(DISTINCT viewer_id) as view_count
                    FROM resource_views
                    GROUP BY resource_id
                ) rv ON r.id = rv.resource_id
                $where_clause
                GROUP BY r.id
                $having_clause
                ORDER BY r.is_pinned DESC, $order_clause
            ";

            // 페이지네이션 적용
            if (isset($params['limit'])) {
                $sql .= " LIMIT ?";
                $query_params[] = $params['limit'];

                if (isset($params['offset'])) {
                    $sql .= " OFFSET ?";
                    $query_params[] = $params['offset'];
                }
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($query_params);
            $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 태그 정보 처리
            foreach ($resources as &$resource) {
                $resource['tags'] = [];
                if (!empty($resource['tag_names']) && !empty($resource['tag_ids'])) {
                    $tag_names = explode(',', $resource['tag_names']);
                    $tag_ids = explode(',', $resource['tag_ids']);
                    foreach ($tag_names as $i => $name) {
                        $resource['tags'][] = [
                            'id' => $tag_ids[$i],
                            'name' => $name
                        ];
                    }
                }
                unset($resource['tag_names'], $resource['tag_ids']);
            }

            return $resources;
        } catch (Exception $e) {
            error_log("Error in search: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 검색 결과 총 개수
     */
    public function countSearch($params = []) {
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

            // 쿼리 실행
            $sql = "
                SELECT COUNT(DISTINCT r.id) as total
                FROM resources r
                LEFT JOIN resource_tags rt ON r.id = rt.resource_id
                LEFT JOIN tags t ON rt.tag_id = t.id
                LEFT JOIN (
                    SELECT resource_id,
                           AVG(rating) as rating_avg,
                           COUNT(DISTINCT viewer_id) as view_count
                    FROM resource_views
                    GROUP BY resource_id
                ) rv ON r.id = rv.resource_id
                $where_clause
                GROUP BY r.id
                $having_clause
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($query_params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)$result['total'];
        } catch (Exception $e) {
            error_log("Error in countSearch: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 모든 태그 조회
     */
    public function getAllTags() {
        try {
            $sql = "
                SELECT t.*, COUNT(rt.resource_id) as resource_count
                FROM tags t
                LEFT JOIN resource_tags rt ON t.id = rt.tag_id
                GROUP BY t.id
                ORDER BY resource_count DESC, t.name ASC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getAllTags: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ID로 리소스 조회 (태그 포함)
     */
    public function getById($id) {
        try {
            $sql = "SELECT r.*, 
                    GROUP_CONCAT(DISTINCT t.name) as tags,
                    u.name as author_name
                FROM {$this->table} r
                LEFT JOIN resource_tags rt ON r.id = rt.resource_id
                LEFT JOIN tags t ON rt.tag_id = t.id
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.id = ?
                GROUP BY r.id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getById: " . $e->getMessage());
            throw new Exception("리소스를 가져오는 중 오류가 발생했습니다.");
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
                WHERE rt.resource_id = ?
                ORDER BY t.name";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$resource_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getTags: " . $e->getMessage());
            throw new Exception("태그를 가져오는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 태그 이름으로 태그 찾기
     */
    public function findTagByName($name) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM tags WHERE name = ?");
            $stmt->execute([$name]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in findTagByName: " . $e->getMessage());
            throw new Exception("태그를 찾는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 새 태그 생성
     */
    public function createTag($name) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO tags (name) VALUES (?)");
            $stmt->execute([$name]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in createTag: " . $e->getMessage());
            throw new Exception("태그를 생성하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스에 태그 추가
     */
    public function addTag($resource_id, $tag_name) {
        try {
            $this->pdo->beginTransaction();

            // 태그가 존재하는지 확인하고 없으면 생성
            $tag = $this->findTagByName($tag_name);
            if (!$tag) {
                $tag_id = $this->createTag($tag_name);
            } else {
                $tag_id = $tag['id'];
            }

            // 리소스-태그 관계 추가
            $stmt = $this->pdo->prepare(
                "INSERT IGNORE INTO resource_tags (resource_id, tag_id) VALUES (?, ?)"
            );
            $result = $stmt->execute([$resource_id, $tag_id]);

            $this->pdo->commit();
            return $result;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error in addTag: " . $e->getMessage());
            throw new Exception("태그를 추가하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스에 기존 태그 추가
     */
    public function addExistingTag($resource_id, $tag_id) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT IGNORE INTO resource_tags (resource_id, tag_id) VALUES (?, ?)"
            );
            return $stmt->execute([$resource_id, $tag_id]);
        } catch (PDOException $e) {
            error_log("Error in addExistingTag: " . $e->getMessage());
            throw new Exception("태그를 추가하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스에서 태그 제거
     */
    public function removeTag($resource_id, $tag_id) {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM resource_tags WHERE resource_id = ? AND tag_id = ?"
            );
            return $stmt->execute([$resource_id, $tag_id]);
        } catch (PDOException $e) {
            error_log("Error in removeTag: " . $e->getMessage());
            throw new Exception("태그를 제거하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 공개/비공개 상태 변경
     */
    public function updateVisibility($resource_id, $is_private) {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE {$this->table} SET is_private = ? WHERE id = ?"
            );
            return $stmt->execute([$is_private, $resource_id]);
        } catch (PDOException $e) {
            error_log("Error in updateVisibility: " . $e->getMessage());
            throw new Exception("리소스 상태를 변경하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 생성
     */
    public function create(array $data): ?array {
        try {
            $this->pdo->beginTransaction();

            // 기본 리소스 정보 저장
            $resource = parent::create($data);

            // 태그 처리
            if (!empty($data['tag_ids'])) {
                foreach ($data['tag_ids'] as $tag_id) {
                    $this->addExistingTag($resource['id'], $tag_id);
                }
            }

            $this->pdo->commit();
            return $resource;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error in create: " . $e->getMessage());
            throw new Exception("리소스를 생성하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 업데이트
     */
    public function update(int $id, array $data): ?array {
        try {
            $this->pdo->beginTransaction();

            // 기본 리소스 정보 업데이트
            $resource = parent::update($id, $data);

            // 태그 처리
            if (isset($data['tag_ids'])) {
                // 기존 태그 모두 제거
                $stmt = $this->pdo->prepare("DELETE FROM resource_tags WHERE resource_id = ?");
                $stmt->execute([$id]);

                // 새 태그 추가
                foreach ($data['tag_ids'] as $tag_id) {
                    $this->addExistingTag($id, $tag_id);
                }
            }

            $this->pdo->commit();
            return $resource;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error in update: " . $e->getMessage());
            throw new Exception("리소스를 수정하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 삭제
     */
    public function delete(int $id): bool {
        try {
            $this->pdo->beginTransaction();

            // 태그 관계 삭제
            $stmt = $this->pdo->prepare("DELETE FROM resource_tags WHERE resource_id = ?");
            $stmt->execute([$id]);

            // 리소스 삭제
            $result = parent::delete($id);

            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error in delete: " . $e->getMessage());
            throw new Exception("리소스를 삭제하는 중 오류가 발생했습니다.");
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
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$resource_id]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
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
            $this->pdo->beginTransaction();

            // 기존 태그 삭제
            $stmt = $this->pdo->prepare("DELETE FROM resource_tags WHERE resource_id = ?");
            $stmt->execute([$resource_id]);

            // 새 태그 추가
            if (!empty($tag_ids)) {
                $values = array_map(function($tag_id) use ($resource_id) {
                    return "($resource_id, $tag_id)";
                }, $tag_ids);
                
                $sql = "INSERT INTO resource_tags (resource_id, tag_id) VALUES " . implode(', ', $values);
                $this->pdo->exec($sql);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error in updateResourceTags: " . $e->getMessage());
            throw new Exception("리소스의 태그를 업데이트하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 조회수 증가 및 조회 기록
     */
    public function recordView($resource_id, $viewer_id) {
        try {
            $this->pdo->beginTransaction();

            // 이미 조회한 기록이 있는지 확인
            $stmt = $this->pdo->prepare("
                SELECT id FROM resource_views 
                WHERE resource_id = ? AND viewer_id = ?
            ");
            $stmt->execute([$resource_id, $viewer_id]);
            $existing = $stmt->fetch();

            if (!$existing) {
                // 새로운 조회 기록 추가
                $stmt = $this->pdo->prepare("
                    INSERT INTO resource_views (resource_id, viewer_id)
                    VALUES (?, ?)
                ");
                $stmt->execute([$resource_id, $viewer_id]);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error in recordView: " . $e->getMessage());
            throw new Exception("조회수를 기록하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 평점 등록/수정
     */
    public function rateResource($resource_id, $viewer_id, $rating) {
        try {
            // 평점 유효성 검사
            if ($rating < 1 || $rating > 5) {
                throw new Exception("평점은 1에서 5 사이의 값이어야 합니다.");
            }

            $this->pdo->beginTransaction();

            // 기존 평점 업데이트 또는 새로운 평점 추가
            $stmt = $this->pdo->prepare("
                INSERT INTO resource_views (resource_id, viewer_id, rating)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = ?
            ");
            $stmt->execute([$resource_id, $viewer_id, $rating, $rating]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error in rateResource: " . $e->getMessage());
            throw new Exception("평점을 등록하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스의 평균 평점 조회
     */
    public function getAverageRating($resource_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT AVG(rating) as avg_rating, COUNT(rating) as rating_count
                FROM resource_views
                WHERE resource_id = ? AND rating IS NOT NULL
            ");
            $stmt->execute([$resource_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
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
            $stmt = $this->pdo->prepare("
                SELECT COUNT(DISTINCT viewer_id) as view_count
                FROM resource_views
                WHERE resource_id = ?
            ");
            $stmt->execute([$resource_id]);
            return $stmt->fetchColumn();
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
            $stmt = $this->pdo->prepare("
                SELECT rating
                FROM resource_views
                WHERE resource_id = ? AND viewer_id = ?
            ");
            $stmt->execute([$resource_id, $user_id]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in getUserRating: " . $e->getMessage());
            throw new Exception("사용자 평점을 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 북마크 추가
     */
    public function addBookmark($resource_id, $user_id, $folder_name = 'default', $note = '') {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO bookmarks (resource_id, user_id, folder_name, note)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE folder_name = ?, note = ?
            ");
            return $stmt->execute([
                $resource_id, $user_id, $folder_name, $note,
                $folder_name, $note
            ]);
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
            $stmt = $this->pdo->prepare("
                DELETE FROM bookmarks 
                WHERE resource_id = ? AND user_id = ?
            ");
            return $stmt->execute([$resource_id, $user_id]);
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
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT folder_name, COUNT(*) as count
                FROM bookmarks
                WHERE user_id = ?
                GROUP BY folder_name
                ORDER BY folder_name
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getBookmarkFolders: " . $e->getMessage());
            throw new Exception("북마크 폴더 목록을 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 사용자의 북마크된 리소스 목록 조회
     */
    public function getBookmarkedResources($user_id, $folder_name = null) {
        try {
            $sql = "
                SELECT r.*, b.folder_name, b.note, b.created_at as bookmarked_at
                FROM resources r
                JOIN bookmarks b ON r.id = b.resource_id
                WHERE b.user_id = ?
            ";
            $params = [$user_id];

            if ($folder_name !== null) {
                $sql .= " AND b.folder_name = ?";
                $params[] = $folder_name;
            }

            $sql .= " ORDER BY b.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getBookmarkedResources: " . $e->getMessage());
            throw new Exception("북마크된 리소스 목록을 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 공유 링크 생성
     */
    public function createShareLink($resource_id, $user_id, $expires_in = null) {
        try {
            $this->pdo->beginTransaction();

            // 기존 공유 링크 확인
            $stmt = $this->pdo->prepare("
                SELECT share_token 
                FROM resource_shares 
                WHERE resource_id = ? AND user_id = ? AND (expires_at IS NULL OR expires_at > NOW())
            ");
            $stmt->execute([$resource_id, $user_id]);
            $existing = $stmt->fetch();

            if ($existing) {
                $this->pdo->commit();
                return $existing['share_token'];
            }

            // 새 공유 링크 생성
            $share_token = bin2hex(random_bytes(32));
            $expires_at = $expires_in ? date('Y-m-d H:i:s', time() + $expires_in) : null;

            $stmt = $this->pdo->prepare("
                INSERT INTO resource_shares (resource_id, user_id, share_token, expires_at)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$resource_id, $user_id, $share_token, $expires_at]);

            $this->pdo->commit();
            return $share_token;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error in createShareLink: " . $e->getMessage());
            throw new Exception("공유 링크를 생성하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 공유 링크로 리소스 조회
     */
    public function getByShareToken($share_token) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT r.*, s.expires_at, s.created_at as shared_at, u.name as shared_by
                FROM resources r
                JOIN resource_shares s ON r.id = s.resource_id
                JOIN users u ON s.user_id = u.id
                WHERE s.share_token = ?
                AND (s.expires_at IS NULL OR s.expires_at > NOW())
            ");
            $stmt->execute([$share_token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getByShareToken: " . $e->getMessage());
            throw new Exception("공유된 리소스를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스가 북마크되었는지 확인
     */
    public function isBookmarked($resource_id, $user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 1 FROM bookmarks 
                WHERE resource_id = ? AND user_id = ?
            ");
            $stmt->execute([$resource_id, $user_id]);
            return (bool)$stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error in isBookmarked: " . $e->getMessage());
            throw new Exception("북마크 상태를 확인하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 최근 공개 리소스를 가져옵니다.
     *
     * @param int $limit 가져올 리소스의 수
     * @return array 리소스 목록 (태그 포함)
     * @throws ModelException
     */
    public function getRecentPublic(int $limit = 5): array
    {
        try {
            // 테이블 존재 여부 확인
            $checkTable = "SHOW TABLES LIKE '{$this->table}'";
            $tableExists = $this->pdo->query($checkTable)->rowCount() > 0;
            
            if (!$tableExists) {
                error_log("Table {$this->table} does not exist");
                return [];
            }

            // 테이블 구조 확인
            $columns = $this->pdo->query("SHOW COLUMNS FROM {$this->table}")->fetchAll(PDO::FETCH_COLUMN);
            error_log("Table columns: " . print_r($columns, true));

            // 가장 기본적인 쿼리로 시작
            $sql = "SELECT * FROM {$this->table} LIMIT :limit";
            
            error_log("Executing SQL: " . $sql);
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($resources) . " resources");
            
            if (empty($resources)) {
                return [];
            }

            // 결과 로깅
            error_log("First resource: " . print_r($resources[0], true));
            
            return $resources;
        } catch (PDOException $e) {
            error_log("Database error in getRecentPublic: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            error_log("Error Info: " . print_r($e->errorInfo, true));
            throw ModelException::findAllError($this->table, [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'sql_state' => $e->errorInfo[0] ?? null,
                'driver_code' => $e->errorInfo[1] ?? null,
                'driver_message' => $e->errorInfo[2] ?? null
            ]);
        }
    }
}
