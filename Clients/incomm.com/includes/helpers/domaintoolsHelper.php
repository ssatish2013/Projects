<?php
abstract class domaintoolsHelper extends baseModel {
	abstract public function getEmailDomain ();
	public function getEmailDomainRegistrant () {
		// returns the name of the owner of the e-mail domain
		return domaintoolsCacheModel::getRegistrant ($this->getEmailDomain ());
	}

	public function getEmailDomainRegistrantCount () {
		// returns the number of domains that the registrant owns
		return domaintoolsCacheModel::getRegistrantCount ($this->getEmailDomain ());
	}
	
	public function getEmailDomainAge () {
		// returns the number of days since the domain was created
		return domaintoolsCacheModel::getAge ($this->getEmailDomain ());
	}
	
	public function getEmailDomainExpires () {
		// returns the number of days until the domain expires (assuming no renewal)
		return domaintoolsCacheModel::getExpires ($this->getEmailDomain ());
	}
	
	public function getEmailDomainID () {
		// returns the ID of the cached record
		return domaintoolsCacheModel::getDataID ($this->getEmailDomain ());
	}
}