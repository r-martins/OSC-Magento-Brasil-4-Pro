<?php
class DeivisonArthur_OnepageCheckout_AjaxController extends Mage_Core_Controller_Front_Action
{
	/*
	* Pega um endereco por cep usando o webservice do Frete.co
	* @author Ricardo Martins <ricardo@ricardomartins.net.br>
	*/
	public function getAddressByZipcodeAction()
	{
		$this->getResponse()->setHeader('Content-type', 'application/json');

		$digits = new Zend_Filter_Digits;
		$zipcode = $this->getRequest()->getParam('zipcode');
		$zipcode = $digits->filter($zipcode);

		//chave do frete.co configurado no painel
        $apikey = Mage::getStoreConfig('onepagecheckout/general/freteco_apikey');
        
        if(empty($apikey))
            return json_encode(array());

		if(empty($zipcode)){
			$this->getResponse()->setBody(json_encode(array()));
			return false;
		}
		
		//retorna no formato da republica virtual (rv)
        $url = 'http://frete.co/api/v1/zipcode/%s?apikey=%s&mode=rv&format=json';
        $url = sprintf($url, $zipcode,$apikey);
		$client = new Zend_Http_Client($url, array(
				'timeout' => 15,
			));

		$response = $client->request();
		

		//se nao for 200 nem 403, da pau
		if(!in_array($client->getLastResponse()->getStatus(), array(200,403))) {
			$this->getResponse()->setHeader('HTTP/1.1','503 Service Temporarily Unavailable');
            $this->getResponse()->setHeader('Status','503 Service Temporarily Unavailable');
            $this->getResponse()->setHeader('Retry-After','5000');
            return false;
		}

		$this->getResponse()->setBody($client->getLastResponse()->getBody());

	}
}