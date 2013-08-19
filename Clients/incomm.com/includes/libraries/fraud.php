<?php

class fraud {

	//fraud reference data
	public $paymentId;
	public $isHot = false;
	public $isWarm = false;
	public $isTrusted = false;
	public $isScreened = false;
	public $isRejected = false;
	public $isSent = false;

	//fraud vars
	public $transaction = null;
	public $user = null;
	public $shoppingCart = null;
	public $messages = array();
	public $gifts = array();
	public $ipAddress = null;
	public $userId = null;
	public $currency = null;
	public $payment = null;

	public $fraudLogId = null;
	public $results = array();
	public $data = array();
	public $rulesUsed = array();
	public $attempts = 0;

	const RULE_SET_CATEGORY = 'fraudRuleSet';
	const RULE_SETTING_CATEGORY = 'fraudRuleSetting';
	const PURCHASE_TRIGGER = 'purchase';
	const POST_AUTH_TRIGGER = 'postAuth';
	const DECISION_TRIGGER = 'decision';
	const POST_PURCHASE_NOTIFY_TRIGGER = 'postPurchaseNotify';
	const POST_INTERNAL_PURCHASE_RULES_TRIGGER = 'postInternalPurchaseRules';

	//flags are used in the final decision making process
	public $flags = array(
		'low'	=> array(),
		'med' => array(),
		'high'	=> array(),
		'screen' => array(),
		'reject' => array(),
		'ignoreScreen' => array(),
		'ignoreReject' => array()
	);

	public function __construct(transactionModel $transaction = null, shoppingCartModel $shoppingCart = null, giftModel $gift = null) { 
		$this->transaction = $transaction;
		$this->shoppingCart = $shoppingCart;
		$this->gift = $gift;

		$this->load();
	}

	/*** LOAD FUNCTION ***/
	//mainly populates fraud log info, gifts, user and messages
	public function load() { 


		//if we're doing a transaction, lets calculate a payment hash
		if($this->transaction) {
			$this->paymentId= $this->transaction->getPaymentHash();
		}

		//if the shopping cart is set (typically a purchase)
		//grab variables associated with it
		if($this->shoppingCart) { 
			
			//see if there's already a fraud log for this transaction
			$fraudLog = new fraudLogModel();
			$fraudLog->shoppingCartId = $this->shoppingCart->id;
			if($fraudLog->load('shoppingCartId')) { 
				$this->fraudLogId = $fraudLog->id;
			}

			//grab messages from the shopping cart
			$this->messages = messageModel::loadAll( $this->shoppingCart );

			//populate gifts based on messages
			$giftIds = array();
			foreach($this->messages as $message) { 
				$this->gifts[] = new giftModel($message->giftId);
			}

			//grab the user from one of our messages (they should all be the same)
			$this->user = new userModel($this->messages[0]->userId);
			$this->userId = $this->user->id;

			//currency
			$this->currency = $this->shoppingCart->currency;
		}
		
		//if the gift is set (typically a claim)
		if($this->gift) {

			//see if there's already a fraud log for this transaction
			$fraudLog = new fraudLogModel();
			$fraudLog->giftId= $this->gift->id;
			if($fraudLog->load('giftId')) { 
				$this->fraudLogId = $fraudLog->id;
			}

			$this->gifts[] = $this->gift;

			//see if there's a user
			$user = new userModel();
			$user->getUserByEmail($this->gift->recipientEmail);
			$this->user = $user;
			$this->userId = $user->id;

			//grab all messages
			$this->messages = messageModel::loadAll( $this->gift );

			$this->currency = $this->gift->currency;
		}

		$this->ipAddress = $_SERVER['HTTP_X_REAL_IP'];
	}


	public function getRisk($trigger, $getData = true) { 
		
		//on claim if it's already approved, just return true
		if(isset($this->gift) && $this->gift->approved) {
			log::info("Gift#" . $this->gift->id . " is already approved, skipping fraud check.");
			return; 
		}

		//this essentially runs all the rules
		if($getData) { 
			$this->getData($trigger);
		}

		$this->doRisk($trigger);

		//get decision making vars 
		$doScreen = count($this->flags['screen']);
		$doReject= count($this->flags['reject']);
		$ignoreScreen = count($this->flags['ignoreScreen']);
		$ignoreReject= count($this->flags['ignoreReject']);

		$log = new fraudLogModel($this->fraudLogId);
		$log->data = $this->data;

		//if we're intent on screening, sending or rejecting
		if($doReject && !$ignoreReject) { 

			//set rejected
			$this->isRejected = true;
			$log->isRejected = true;

			//force is sent (in case it was previously set)
			$log->isSent = false;
			$this->isSent = false;
			log::info('Gift fraud status - REJECTED.');
			$log->save();
			throw new fraudRejectException();
			return;
		}

		if($doScreen && !$ignoreScreen) { 

			//set screened
			$this->isScreened = true;
			$log->isScreened = true;

			//force is sent (in case it was previously set)
			$log->isSent = false;
			$this->isSent = false;
			$log->save();
			log::info('Gift fraud status - SCREEN.');
			return;
		}

		log::info('Gift fraud status - OK.');
		$log->isSent = true;
		$log->save();
		$this->isSent = true;
	}

	public function doRisk($trigger) {
		log::debug("doRisk(trigger=$trigger)");

		//initial count of some vars
		$lowCount = count($this->flags['low']);
		$medCount = count($this->flags['med']);
		$highCount = count($this->flags['high']);
		$total = count($this->rulesUsed);

		//calculate some scores
		$decisionSettings = settingModel::getPartnerSettings(globals::partner(), "fraudDecisionSetting");
		$score = $decisionSettings['startScore'];
		$score -= $lowCount * $decisionSettings['lowWeight'];
		$score -= $medCount * $decisionSettings['medWeight'];
		$score -= $highCount * $decisionSettings['highWeight'];
			
		if($total == 0) { $total = 1; }

		//get the percentage and scores
		$riskObj = array(
			'lowPercentage' => ($lowCount/$total)*100,
			'medPercentage' => ($medCount/$total)*100,
			'highPercentage' => ($highCount/$total)*100,
			'score' => $score,
			'startScore' => $decisionSettings['startScore'],
			'lowWeight' => $decisionSettings['lowWeight'],
			'medWeight' => $decisionSettings['medWeight'],
			'highWeight' => $decisionSettings['highWeight'],
			'originalScorePercentage' => ($score/$decisionSettings['startScore'])*100
		);
		$this->data['DecisionScore'] = $riskObj;

		//grab all decision rules
		$decisionRuleSets = ruleSetModel::getRuleSets(self::DECISION_TRIGGER, self::RULE_SET_CATEGORY, true);
		//log::debug("Rule sets, trigger=" . self::DECISION_TRIGGER . ", category=" . self::RULE_SET_CATEGORY . ", " . 
		//		print_r($decisionRuleSets, true));
		foreach($decisionRuleSets as $ruleSet ) { 

			//run through the rule set
			$this->rulesUsed[] = $ruleSet->getPiiValues();
			foreach($ruleSet->rules as $rule) { 

				//evaluate && action
				$action = $ruleSet->evaluate($this->data);
				if($action) {
					$this->$action($ruleSet);
				}
			}
		}

		$log = new fraudLogModel($this->fraudLogId);

		$this->data['flags'] = $this->flags;
		$this->data['AttemptsFraud']['rejectReasons'] = $log->data['AttemptsFraud']['rejectReasons'];
		foreach($this->flags['reject'] as $ruleName => $data) {
			$this->data['AttemptsFraud']['rejectReasons'][] = $ruleName;
		}

		$log->data = $this->data;

		//set other flags
		$logFlags = array(
			'isHot', 'isRejected', 'isScreened',
			'isSent', 'isTrusted', 'isWarm',
			'rulesUsed',
		);
		foreach($logFlags as $flagName) { 
			$log->$flagName = $this->$flagName;
		}
		$log->save();
	}

	public function getData($trigger) { 
		if($trigger == self::PURCHASE_TRIGGER) { 
			$this->getPurchaseData($trigger);
		}
	}

	public function getPurchaseData($trigger = self::PURCHASE_TRIGGER) { 

		$log = new fraudLogModel();
		if($this->fraudLogId !== null) { 
			$log->id = $this->fraudLogId;
			$log->load();
		}
		$log->logType = $trigger;
		$log->userId = $this->user->id;
		$log->currency = $this->currency;
		$log->ipAddress = $_SERVER['HTTP_X_REAL_IP'];
		$log->transactionId = $this->transaction->id;
		$log->shoppingCartId = $this->shoppingCart->id;
		$log->messages = array();
		$log->gifts = array();
		$log->paymentId = $this->paymentId;

		foreach($this->messages as $message) { 
			$log->messages[] = $message->getPiiValues();
		}
		foreach($this->gifts as $gift) { 
			$log->gifts[] = $gift->getPiiValues();
		}

		if(!isset($log->data['AttemptsFraud'])) { 
			$log->data['AttemptsFraud'] = array(
				'attempts' => 0,
				'rejectReasons' => array()
			);
		}
		$this->data['AttemptsFraud'] = $log->data['AttemptsFraud'];
		$log->save();

		//grab all rule sets to process
		//the category `fraudRule` should just say whether or not 
		//a rule set is enabled/disabled for a partner
		$fraudRuleSets = ruleSetModel::getRuleSets($trigger, self::RULE_SET_CATEGORY, true);
		foreach($fraudRuleSets as $ruleSet) { 

			//grab the rule set
			//$ruleSet = new ruleSetModel($fraudRuleSet);
			$this->rulesUsed[] = $ruleSet->getPiiValues();

			//if we don't have the data we need for this rule, grab it
			foreach($ruleSet->rules as $rule) { 
				
				//if we don't have the plugin loaded
				$pluginClass = $rule->subCategory;
				if(!isset($this->data[$pluginClass])) { 

					//load up the plugin
					require_once("fraud/$pluginClass.php");
					$plugin = new $pluginClass($this);
					$this->data[$pluginClass] = $plugin->getData();
				}
			}

			$action = $ruleSet->evaluate($this->data);
			if($action) { 
				$this->$action($ruleSet);
			}
		}
		$this->data['flags'] = $this->flags;
		$this->data['AttemptsFraud']['attempts'] = $log->data['AttemptsFraud']['attempts'] + 1;
		$this->data['AttemptsFraud']['rejectReasons'] = $log->data['AttemptsFraud']['rejectReasons'];

		//go through the old log (if there is one) and append any data we
		//previously collected
		foreach($log->data as $key => $value) {
			if(!isset($this->data[$key])) { $this->data[$key] = $value; }
		}
		$log->data = $this->data;
		$log->save();
		$this->fraudLogId = $log->id;
	}

	//basically takes in a trigger (i.e. postPurchase) and runs the rules against
	//the object. This assumes that the fraud object is properly loaded/populated.
	//Then it immediately re-evaluates the gift and gives a new result
	public function addRisk($trigger) { 

		//grab all the rules we need to process for this transaction
		$fraudRuleSets = ruleSetModel::getRuleSets($trigger, self::RULE_SET_CATEGORY, true);
		$reset = array();

		foreach($fraudRuleSets as $ruleSet) { 

			$this->rulesUsed[] = $ruleSet->getPiiValues();

			foreach($ruleSet->rules as $rule) { 

				$pluginClass = $rule->subCategory;
				require_once("fraud/$pluginClass.php");
				$plugin = new $pluginClass($this);

				$doReset = false;
				if(method_exists($plugin, "doReset") && !isset($reset[$pluginClass])) { 
					$doReset = $plugin->doReset();
					$reset[$pluginClass] = 1;
				}

				if(!isset($this->data[$pluginClass]) || $doReset) { 

					//get data if there's absolutely no data
					$this->data[$pluginClass] = $plugin->getData();
				}
			}

			$action = $ruleSet->evaluate($this->data);
			if($action) { 
				$this->$action($ruleSet);
			}
		}

		//re evaluate, use false so we don't re-load or re-run all the other fraud filters
		$this->getRisk($trigger, false);
	}
	
	/*
 	 * Rule Actions
 	 */
	public function doNothing($ruleSet) {
		/* placeholder funciton, doesn't do anything */
	}

	public function markLowRisk($ruleSet) { 
		$this->flags['low'][$ruleSet->guid] = $ruleSet->evaluateValues;
	}

	public function markMedRisk($ruleSet) { 
		$this->flags['med'][$ruleSet->guid] = $ruleSet->evaluateValues;
	}

	public function markHighRisk($ruleSet) { 
		$this->flags['high'][$ruleSet->guid] = $ruleSet->evaluateValues;
	}

	public function doScreen($ruleSet) {
		$this->isScreened = true;
		$this->flags['screen'][$ruleSet->guid] = $ruleSet->evaluateValues;
	}

	public function doReject($ruleSet) {
		$this->isRejected= true;
		$this->flags['reject'][$ruleSet->guid] = $ruleSet->evaluateValues;
	}

	public function doSend($ruleSet) {
		$this->isSent = true;
		$this->flags['ignoreScreen'][$ruleSet->guid] = $ruleSet->evaluateValues;
		$this->flags['ignoreReject'][$ruleSet->guid] = $ruleSet->evaluateValues;
	}

	public function ignoreScreen($ruleSet) {
		$this->flags['ignoreScreen'][$ruleSet->guid] = $ruleSet->evaluateValues;
	}

	public function ignoreReject($ruleSet) {
		$this->flags['ignoreReject'][$ruleSet->guid] = $ruleSet->evaluateValues;
	}
}

