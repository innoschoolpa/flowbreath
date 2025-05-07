<?php
namespace Middleware;

/**
 * 미들웨어 기본 클래스
 */
abstract class Middleware {
    /**
     * 다음 미들웨어 핸들러
     */
    protected $next;

    /**
     * 다음 미들웨어 설정
     */
    public function setNext(Middleware $handler): Middleware {
        $this->next = $handler;
        return $handler;
    }

    /**
     * 다음 미들웨어로 요청 전달
     */
    protected function handleNext() {
        if ($this->next) {
            return $this->next->handle();
        }
        return true;
    }

    /**
     * 미들웨어 처리 메소드
     * 각 미들웨어 클래스에서 구현해야 함
     */
    abstract public function handle();
} 