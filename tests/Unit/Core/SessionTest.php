<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Session;
use App\Core\Application;

class SessionTest extends TestCase
{
    private $session;
    private $testData = [
        'string' => 'test string',
        'array' => ['key' => 'value'],
        'object' => (object)['property' => 'value'],
        'number' => 123
    ];

    protected function setUp(): void
    {
        // 테스트용 설정
        $config = [
            'session' => [
                'lifetime' => 3600,
                'path' => sys_get_temp_dir(),
                'encryption_key' => 'test_key_123',
                'cookie' => [
                    'name' => 'test_session',
                    'path' => '/',
                    'domain' => null,
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            ]
        ];

        // Application 인스턴스 설정
        $app = Application::getInstance();
        $app->setConfig('app', $config);

        $this->session = Session::getInstance();
    }

    protected function tearDown(): void
    {
        $this->session->clear();
    }

    public function testSessionInitialization()
    {
        $this->assertTrue($this->session->isStarted());
        $this->assertNotEmpty($this->session->id());
    }

    public function testSetAndGet()
    {
        foreach ($this->testData as $key => $value) {
            $this->session->set($key, $value);
            $this->assertEquals($value, $this->session->get($key));
        }
    }

    public function testGetWithDefault()
    {
        $this->assertNull($this->session->get('non_existent'));
        $this->assertEquals('default', $this->session->get('non_existent', 'default'));
    }

    public function testHas()
    {
        $this->session->set('test_key', 'test_value');
        $this->assertTrue($this->session->has('test_key'));
        $this->assertFalse($this->session->has('non_existent'));
    }

    public function testRemove()
    {
        $this->session->set('test_key', 'test_value');
        $this->assertTrue($this->session->has('test_key'));
        
        $this->session->remove('test_key');
        $this->assertFalse($this->session->has('test_key'));
    }

    public function testClear()
    {
        foreach ($this->testData as $key => $value) {
            $this->session->set($key, $value);
        }

        $this->session->clear();
        
        foreach ($this->testData as $key => $value) {
            $this->assertFalse($this->session->has($key));
        }
    }

    public function testFlashMessages()
    {
        $this->session->flash('success', 'Operation successful');
        $this->assertTrue($this->session->hasFlash('success'));
        $this->assertEquals('Operation successful', $this->session->getFlash('success'));
        
        // 플래시 메시지는 한 번만 사용 가능
        $this->assertFalse($this->session->hasFlash('success'));
    }

    public function testRegenerate()
    {
        $oldId = $this->session->id();
        $this->session->set('test_key', 'test_value');
        
        $this->session->regenerate();
        
        $this->assertNotEquals($oldId, $this->session->id());
        $this->assertEquals('test_value', $this->session->get('test_key'));
    }

    public function testSessionExpiration()
    {
        $this->session->set('test_key', 'test_value');
        
        // 세션 만료 시간을 1초로 설정
        $this->session->setGcMaxLifetime(1);
        sleep(2);
        
        // 세션 만료 체크
        $this->session->checkExpiration();
        
        $this->assertFalse($this->session->has('test_key'));
    }

    public function testEncryption()
    {
        $sensitiveData = [
            'password' => 'secret123',
            'token' => 'sensitive_token',
            'credit_card' => '4111111111111111'
        ];

        foreach ($sensitiveData as $key => $value) {
            $this->session->set($key, $value);
            $retrieved = $this->session->get($key);
            $this->assertEquals($value, $retrieved);
            
            // 원시 세션 데이터가 암호화되어 있는지 확인
            $rawData = $_SESSION[$key];
            $this->assertNotEquals($value, $rawData);
            $this->assertTrue(base64_decode($rawData) !== false);
        }
    }

    public function testAll()
    {
        foreach ($this->testData as $key => $value) {
            $this->session->set($key, $value);
        }

        $allData = $this->session->all();
        $this->assertIsArray($allData);
        $this->assertCount(count($this->testData), $allData);
    }
} 