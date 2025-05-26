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
        // 댓글 작성 폼
        const commentForm = document.getElementById('comment-form');
        if (commentForm) {
            commentForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.createComment();
            });
        }

        // 무한 스크롤
        window.addEventListener('scroll', () => {
            if (this.loading || !this.hasMore) return;
            
            const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
            if (scrollTop + clientHeight >= scrollHeight - 100) {
                this.loadComments();
            }
        });
    }

    async loadComments() {
        if (this.loading) return;
        this.loading = true;

        try {
            const response = await fetch(`/api/resources/${this.resourceId}/comments?page=${this.page}`);
            const data = await response.json();

            if (data.success) {
                this.renderComments(data.data);
                this.page++;
                this.hasMore = data.data.length === 10;
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            this.showError('댓글을 불러오는데 실패했습니다.');
        } finally {
            this.loading = false;
        }
    }

    async createComment() {
        const form = document.getElementById('comment-form');
        const content = form.querySelector('textarea[name="content"]').value;
        const parentId = form.querySelector('input[name="parent_id"]')?.value;

        if (!content.trim()) {
            this.showError('댓글 내용을 입력해주세요.');
            return;
        }

        try {
            const response = await fetch('/api/comments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    resource_id: this.resourceId,
                    content,
                    parent_id: parentId || null
                })
            });

            const data = await response.json();

            if (data.success) {
                form.reset();
                this.showSuccess(data.message);
                this.page = 1;
                this.loadComments();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            this.showError('댓글 작성에 실패했습니다.');
        }
    }

    async updateComment(id, content) {
        try {
            const response = await fetch(`/api/comments/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ content })
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.message);
                this.page = 1;
                this.loadComments();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            this.showError('댓글 수정에 실패했습니다.');
        }
    }

    async deleteComment(id) {
        if (!confirm('정말로 이 댓글을 삭제하시겠습니까?')) return;

        try {
            const response = await fetch(`/api/comments/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.message);
                this.page = 1;
                this.loadComments();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
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
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ reason })
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.message);
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            this.showError('댓글 신고에 실패했습니다.');
        }
    }

    async addReaction(id, type) {
        try {
            const response = await fetch(`/api/comments/${id}/reactions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ reaction_type: type })
            });

            const data = await response.json();

            if (data.success) {
                this.page = 1;
                this.loadComments();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            this.showError('반응 등록에 실패했습니다.');
        }
    }

    async removeReaction(id) {
        try {
            const response = await fetch(`/api/comments/${id}/reactions`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                this.page = 1;
                this.loadComments();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            this.showError('반응 제거에 실패했습니다.');
        }
    }

    renderComments(comments) {
        const container = document.getElementById('comments-container');
        if (!container) return;

        if (this.page === 1) {
            container.innerHTML = '';
        }

        comments.forEach(comment => {
            const commentElement = this.createCommentElement(comment);
            container.appendChild(commentElement);
        });
    }

    createCommentElement(comment) {
        const div = document.createElement('div');
        div.className = 'comment';
        div.style.marginLeft = `${comment.depth * 20}px`;

        const isAuthor = comment.user_id === window.currentUserId;
        const isAdmin = window.isAdmin;

        div.innerHTML = `
            <div class="comment-header">
                <span class="comment-author">${comment.author_name}</span>
                <span class="comment-time">${this.formatTime(comment.created_at)}</span>
            </div>
            <div class="comment-content">${comment.content}</div>
            <div class="comment-actions">
                <button class="like" onclick="commentManager.addReaction(${comment.id}, 'like')">
                    👍 ${comment.like_count || 0}
                </button>
                <button class="dislike" onclick="commentManager.addReaction(${comment.id}, 'dislike')">
                    👎 ${comment.dislike_count || 0}
                </button>
                <button class="reply" onclick="commentManager.showReplyForm(${comment.id})">
                    답글
                </button>
                ${this.canModify(comment) ? `
                    <button class="edit" onclick="commentManager.showEditForm(${comment.id})">
                        수정
                    </button>
                    <button class="delete" onclick="commentManager.deleteComment(${comment.id})">
                        삭제
                    </button>
                ` : ''}
                <button class="report" onclick="commentManager.reportComment(${comment.id})">
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
        return window.currentUserId === comment.user_id || window.isAdmin;
    }

    showReplyForm(parentId) {
        const form = document.getElementById('comment-form');
        form.querySelector('input[name="parent_id"]').value = parentId;
        form.querySelector('textarea[name="content"]').focus();
    }

    showEditForm(id) {
        const comment = document.querySelector(`.comment[data-id="${id}"]`);
        const content = comment.querySelector('.comment-content').textContent;
        
        comment.querySelector('.comment-content').innerHTML = `
            <textarea class="edit-content">${content}</textarea>
            <button onclick="commentManager.updateComment(${id}, this.previousElementSibling.value)">
                저장
            </button>
            <button onclick="commentManager.loadComments()">취소</button>
        `;
    }

    showSuccess(message) {
        // 성공 메시지 표시 로직
        alert(message);
    }

    showError(message) {
        // 에러 메시지 표시 로직
        alert(message);
    }
}

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', () => {
    const resourceId = document.body.dataset.resourceId;
    if (resourceId) {
        window.commentManager = new CommentManager(resourceId);
    }
});

// Comments functionality
document.addEventListener('DOMContentLoaded', function() {
    const commentForm = document.getElementById('comment-form');
    const commentsContainer = document.getElementById('comments-container');
    const loadingIndicator = document.querySelector('.loading');
    let currentPage = 1;
    let isLoading = false;
    let hasMoreComments = true;
    const resourceId = window.location.pathname.split('/').pop();

    // Comment form submission
    if (commentForm) {
        commentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const content = formData.get('content').trim();
            const parentId = formData.get('parent_id');

            if (!content) {
                alert('댓글 내용을 입력해주세요.');
                return;
            }

            try {
                const response = await fetch(`/api/resources/${resourceId}/comments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        content: content,
                        parent_id: parentId || null
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    this.reset();
                    commentsContainer.innerHTML = '';
                    currentPage = 1;
                    hasMoreComments = true;
                    await loadComments(currentPage);
                    alert(result.message);
                } else {
                    alert(result.message || '댓글 작성 중 오류가 발생했습니다.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('댓글 작성 중 오류가 발생했습니다.');
            }
        });
    }

    // Load comments
    async function loadComments(page = 1) {
        if (isLoading || !hasMoreComments) return;
        
        isLoading = true;
        loadingIndicator.style.display = 'block';

        try {
            const response = await fetch(`/api/resources/${resourceId}/comments?page=${page}`);
            const data = await response.json();

            if (data.success) {
                if (page === 1) {
                    commentsContainer.innerHTML = '';
                }
                
                data.data.comments.forEach(comment => {
                    const commentElement = createCommentElement(comment);
                    commentsContainer.appendChild(commentElement);
                });

                hasMoreComments = data.data.comments.length === 10;
                currentPage = page;
                updateCommentCount();
            }
        } catch (error) {
            console.error('Error:', error);
        } finally {
            isLoading = false;
            loadingIndicator.style.display = 'none';
        }
    }

    // Create comment element
    function createCommentElement(comment) {
        const div = document.createElement('div');
        div.className = 'comment';
        div.dataset.commentId = comment.id;
        
        const isAuthor = comment.user_id === window.currentUserId;
        const isAdmin = window.isAdmin;

        div.innerHTML = `
            <div class="comment-header">
                <span class="comment-author">${comment.author_name}</span>
                <span class="comment-date">${new Date(comment.created_at).toLocaleString()}</span>
            </div>
            <div class="comment-content">${comment.content}</div>
            <div class="comment-actions">
                <button class="comment-action-btn reply-btn" onclick="showReplyForm(${comment.id})">
                    <i class="fas fa-reply"></i> ${window.currentLang === 'en' ? 'Reply' : '답글'}
                </button>
                ${(isAuthor || isAdmin) ? `
                    <button class="comment-action-btn edit-btn" onclick="editComment(${comment.id}, '${comment.content.replace(/'/g, "\\'")}')">
                        <i class="fas fa-edit"></i> ${window.currentLang === 'en' ? 'Edit' : '수정'}
                    </button>
                    <button class="comment-action-btn delete-btn" onclick="deleteComment(${comment.id})">
                        <i class="fas fa-trash"></i> ${window.currentLang === 'en' ? 'Delete' : '삭제'}
                    </button>
                ` : ''}
            </div>
            <div id="reply-form-${comment.id}" class="reply-form">
                <form class="comment-form">
                    <input type="hidden" name="parent_id" value="${comment.id}">
                    <textarea name="content" placeholder="${window.currentLang === 'en' ? 'Write a reply...' : '답글을 입력하세요...'}" maxlength="1000" required></textarea>
                    <div class="d-flex gap-2">
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i>
                            ${window.currentLang === 'en' ? 'Post Reply' : '답글 작성'}
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="hideReplyForm(${comment.id})">
                            <i class="fas fa-times"></i> ${window.currentLang === 'en' ? 'Cancel' : '취소'}
                        </button>
                    </div>
                </form>
            </div>
            <div id="replies-${comment.id}" class="replies-container"></div>
        `;

        // Setup reply form
        const replyForm = div.querySelector(`#reply-form-${comment.id} form`);
        setupReplyForm(replyForm, comment.id);

        // Display nested replies
        if (comment.replies && comment.replies.length > 0) {
            const repliesContainer = div.querySelector(`#replies-${comment.id}`);
            comment.replies.forEach(reply => {
                const replyElement = createCommentElement(reply);
                replyElement.classList.add('reply');
                repliesContainer.appendChild(replyElement);
            });
        }

        return div;
    }

    // Setup reply form
    function setupReplyForm(form, commentId) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const content = formData.get('content').trim();

            if (!content) {
                alert('답글 내용을 입력해주세요.');
                return;
            }

            await handleReplySubmit(commentId, content);
            this.reset();
            hideReplyForm(commentId);
        });
    }

    // Show reply form
    window.showReplyForm = function(commentId) {
        const replyForm = document.getElementById(`reply-form-${commentId}`);
        const allReplyForms = document.querySelectorAll('.reply-form');
        
        allReplyForms.forEach(form => {
            if (form.id !== `reply-form-${commentId}`) {
                form.classList.remove('active');
            }
        });

        replyForm.classList.add('active');
        replyForm.querySelector('textarea').focus();
    };

    // Hide reply form
    window.hideReplyForm = function(commentId) {
        const replyForm = document.getElementById(`reply-form-${commentId}`);
        replyForm.classList.remove('active');
    };

    // Handle reply submission
    async function handleReplySubmit(commentId, content) {
        try {
            const response = await fetch(`/api/resources/${resourceId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    content: content,
                    parent_id: commentId
                })
            });

            const result = await response.json();
            
            if (result.success) {
                commentsContainer.innerHTML = '';
                currentPage = 1;
                hasMoreComments = true;
                await loadComments(currentPage);
                alert(result.message);
            } else {
                alert(result.message || '답글 작성 중 오류가 발생했습니다.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('답글 작성 중 오류가 발생했습니다.');
        }
    }

    // Update comment count
    function updateCommentCount() {
        const count = document.querySelectorAll('.comment').length;
        const countElement = document.querySelector('.comments-section h3');
        countElement.innerHTML = `<i class="fas fa-comments"></i> 댓글 (${count})`;
    }

    // Infinite scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && hasMoreComments) {
                loadComments(currentPage + 1);
            }
        });
    });

    observer.observe(loadingIndicator);

    // Initial load
    loadComments();
}); 