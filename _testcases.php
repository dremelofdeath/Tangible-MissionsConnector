<?php

// CMC Test Cases... by Zachary Murray
// (C) Tangible, LLC. All rights reserved.
// All information herein is confidential. DO NOT DISCLOSE.

include_once 'common.php';

echo 'CMC Test Case Frontend. Copyright 2010 Tangible, LLC.<br/><br/>';
cmc_startup($appapikey, $appsecret);
echo '<br/>Running all testcases!<br/><br/>';

abstract class CMCTestCase {
  private static $isTestRunning = false;
  abstract public function run();
  protected function startTest($message) {
    if($isTestRunning) {
      $this->killTest();
    } else {
      echo '['.get_class($this).'] '.$message.' ';
    }
  }
  private function killTest() {
    if($isTestRunning) {
      echo '<b>killed.</b><br/>';
    }
    $isTestRunning = false;
  }
  protected function signalTestFailure($reason = null) {
    echo '<em>failed</em>';
    if($reason != null) {
      echo ' (reason given: $reason)';
    }
    echo '.<br/>';
    $isTestRunning = false;
  }
  protected function signalTestPassed() {
    echo 'passed!<br/>';
    $isTestRunning = false;
  }
  protected function signalTestResult($result, $reason = null) {
    if($result) {
      $this->signalTestPassed();
    } else {
      $this->signalTestFailure($reason);
    }
  }
  private function testlog($message) {
    echo '['.get_class($this).']     '.$message.'<br/>';
  }
}

class CMCDBCheckUserTC extends CMCTestCase { // tests db_check_user in common.php
  private static $nonexistentUser = '9999999999999';
  private function cleanup() {
    $sql = "DELETE FROM users WHERE userid=".self::$nonexistentUser.';';
    mysql_query($sql) or die(mysql_error());
  }
  public function run() {
    $this->startTest("Checking a user that definitely exists...");
    $check = db_check_user('25826994'); // Zachary Murray's user ID
    $this->signalTestResult($check);
    $this->startTest("Checking a user that definitely does NOT exist...");
    $check = db_check_user(self::$nonexistentUser);
    $this->signalTestResult(!$check);
    $this->cleanup();
  }
}

$test = new CMCDBCheckUserTC();
$test->run();
