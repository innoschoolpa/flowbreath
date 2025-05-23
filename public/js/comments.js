class CommentManager {
    constructor(resourceId) {
        this.resourceId = resourceId;
        this.page = 1;
        this.loading = false;
        this.hasMore = true;
        this.setupEventListeners();
        this.loadComments();
    }

    setupEventListeners() {
        // 댓글 작성 폼 제출
        document.getElementById('commentForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.createComment();
        });

        // 무한 스크롤
        window.addEventListener('scroll', () => {
            if (this.loading || !this.hasMore) return;
            
            const {scrollTop, scrollHeight, clientHeight} = document.documentElement;
            if (scrollTop + clientHeight >= scrollHeight - 5) {
                this.loadMoreComments();
            }
        });
    }

    async loadComments() {
        try {
            this.loading = true;
            const response = await fetch(`/api/resources/${this.resourceId}/comments?page=${this.page}`);
            const data = await response.json();
            
            if (data.comments.length === 0) {
                this.hasMore = false;
                return;
            }

            this.renderComments(data.comments);
            this.page++;
        } catch (error) {
            console.error('댓글 로딩 실패:', error);
            this.showError('댓글을 불러오는데 실패했습니다.');
        } finally {
            this.loading = false;
        }
    }

    async createComment() {
        const form = document.getElementById('commentForm');
        const content = form.querySelector('[name="content"]').value;
        const parentId = form.querySelector('[name="parent_id"]')?.value;

        if (!content.trim()) {
            this.showError('댓글 내용을 입력해주세요.');
            return;
        }

        try {
            const response = await fetch('/api/comments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    resource_id: this.resourceId,
                    content,
                    parent_id: parentId
                })
            });

            const data = await response.json();
            
            if (response.ok) {
                form.reset();
                this.showSuccess('댓글이 작성되었습니다.');
                this.refreshComments();
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            console.error('댓글 작성 실패:', error);
            this.showError('댓글 작성에 실패했습니다.');
        }
    }

    async updateComment(id, content) {
        try {
            const response = await fetch(`/api/comments/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ content })
            });

            const data = await response.json();
            
            if (response.ok) {
                this.showSuccess('댓글이 수정되었습니다.');
                this.refreshComments();
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            console.error('댓글 수정 실패:', error);
            this.showError('댓글 수정에 실패했습니다.');
        }
    }

    async deleteComment(id) {
        if (!confirm('정말로 이 댓글을 삭제하시겠습니까?')) return;

        try {
            const response = await fetch(`/api/comments/${id}`, {
                method: 'DELETE'
            });

            const data = await response.json();
            
            if (response.ok) {
                this.showSuccess('댓글이 삭제되었습니다.');
                this.refreshComments();
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            console.error('댓글 삭제 실패:', error);
            this.showError('댓글 삭제에 실패했습니다.');
        }
    }

    async reportComment(id) {
        const reason = prompt('신고 사유를 입력해주세요:');
        if (!reason) return;

        try {
            const response = await fetch(`/api/comments/${id}/report`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reason })
            });

            const data = await response.json();
            
            if (response.ok) {
                this.showSuccess('댓글이 신고되었습니다.');
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            console.error('댓글 신고 실패:', error);
            this.showError('댓글 신고에 실패했습니다.');
        }
    }

    async addReaction(id, type) {
        try {
            const response = await fetch(`/api/comments/${id}/reactions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reaction_type: type })
            });

            const data = await response.json();
            
            if (response.ok) {
                this.refreshComments();
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            console.error('반응 추가 실패:', error);
            this.showError('반응 추가에 실패했습니다.');
        }
    }

    async removeReaction(id) {
        try {
            const response = await fetch(`/api/comments/${id}/reactions`, {
                method: 'DELETE'
            });

            const data = await response.json();
            
            if (response.ok) {
                this.refreshComments();
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            console.error('반응 제거 실패:', error);
            this.showError('반응 제거에 실패했습니다.');
        }
    }

    renderComments(comments) {
        const container = document.getElementById('commentsContainer');
        
        comments.forEach(comment => {
            const commentElement = this.createCommentElement(comment);
            container.appendChild(commentElement);
        });
    }

    createCommentElement(comment) {
        const div = document.createElement('div');
        div.className = 'comment';
        div.style.marginLeft = `${comment.depth * 20}px`;
        
        div.innerHTML = `
            <div class="comment-header">
                <span class="comment-author">${comment.user_name}</span>
                <span class="comment-time">${this.formatTime(comment.created_at)}</span>
            </div>
            <div class="comment-content">${comment.content}</div>
            <div class="comment-actions">
                <button onclick="commentManager.addReaction(${comment.id}, 'like')">
                    좋아요 (${comment.like_count})
                </button>
                <button onclick="commentManager.addReaction(${comment.id}, 'dislike')">
                    싫어요 (${comment.dislike_count})
                </button>
                ${comment.depth < 5 ? `
                    <button onclick="commentManager.showReplyForm(${comment.id})">
                        답글
                    </button>
                ` : ''}
                ${this.canModify(comment) ? `
                    <button onclick="commentManager.showEditForm(${comment.id})">
                        수정
                    </button>
                    <button onclick="commentManager.deleteComment(${comment.id})">
                        삭제
                    </button>
                ` : ''}
                <button onclick="commentManager.reportComment(${comment.id})">
                    신고
                </button>
            </div>
        `;
        
        return div;
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        
        if (days > 0) return `${days}일 전`;
        if (hours > 0) return `${hours}시간 전`;
        if (minutes > 0) return `${minutes}분 전`;
        return '방금 전';
    }

    canModify(comment) {
        // 현재 로그인한 사용자의 ID를 가져오는 로직 필요
        const currentUserId = document.body.dataset.userId;
        return currentUserId && (currentUserId === comment.user_id || document.body.dataset.isAdmin === 'true');
    }

    showReplyForm(parentId) {
        const form = document.getElementById('commentForm');
        form.querySelector('[name="parent_id"]').value = parentId;
        form.scrollIntoView({ behavior: 'smooth' });
    }

    showEditForm(id) {
        const comment = document.querySelector(`[data-comment-id="${id}"]`);
        const content = comment.querySelector('.comment-content').textContent;
        
        const form = document.createElement('form');
        form.innerHTML = `
            <textarea name="content">${content}</textarea>
            <button type="submit">수정</button>
            <button type="button" onclick="this.parentElement.remove()">취소</button>
        `;
        
        form.onsubmit = (e) => {
            e.preventDefault();
            const newContent = form.querySelector('[name="content"]').value;
            this.updateComment(id, newContent);
            form.remove();
        };
        
        comment.querySelector('.comment-content').replaceWith(form);
    }

    showError(message) {
        // 에러 메시지 표시 로직
        alert(message);
    }

    showSuccess(message) {
        // 성공 메시지 표시 로직
        alert(message);
    }

    refreshComments() {
        const container = document.getElementById('commentsContainer');
        container.innerHTML = '';
        this.page = 1;
        this.hasMore = true;
        this.loadComments();
    }
}

// 페이지 로드 시 댓글 매니저 초기화
document.addEventListener('DOMContentLoaded', () => {
    const resourceId = document.body.dataset.resourceId;
    window.commentManager = new CommentManager(resourceId);
}); 