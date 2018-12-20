Interface extends BaseIntegrationInterface

    public function getEndPoint($type);

    public function checkStatus();

    public function upateDataModel();

    public function postBodyRequest($body, $endpoint, $overrides = array());

    public function getResource();
