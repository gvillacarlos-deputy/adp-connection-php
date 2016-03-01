<?php

$adp_logging = 1;
$adp_logmode = 0;
$adp_logfile = "/tmp/adpapi.log";


$libroot = realpath("../../") . "/";
$webroot = realpath("../client") . "/";

require_once("../adpapiUtility.class.php");



class adpapiUtliltyTest extends \PHPUnit_Framework_Testcase
{

	//----------------------------------------------
	// Test creation factory with valid grant type
	//----------------------------------------------

	public function testFactoryGood() {

		// Get the factory non-staticaly for this.
		// Just tests getting it, since the factory isnt trackable with phpunit.

		$factory = new adpapiUtilityFactory();
		$logger = $factory->getObject("logger");

		$this->assertInstanceOf('adpapiUtilityLogger', $logger);


	}

	//----------------------------------------------
	// Test a write to the logger
	//----------------------------------------------

	public function testLogging() {


		$logger = new adpapiUtilityLogger();
		$logger->logmode = 1;

		$logger->write("This is a test");

		$this->assertTrue(TRUE);


	}

	public function testCreateException() {

		$except = new adpException("Message", 0 , null, "Package from remote");

		$this->assertInstanceOf('adpException', $except);

	}

	public function testExceptionMethods() {

		$except = new adpException("Message", 0 , null, "Package from remote");

		$test1 = $except->getStatus();
		$test2 = $except->getResponse();
		$test3 = $except->__toString();

		$this->assertTrue(TRUE);

	}
}










