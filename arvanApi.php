<?php
	/**
	 * Created by tokapps TM.
	 * Programmer: gholamreza beheshtian
	 * Mobile:09353466620
	 * Company Phone:05138846411
	 * Site:http://tokapps.ir
	 * Date: ۰۱/۲۲/۲۰۲۰
	 * Time: ۱۳:۳۰ بعدازظهر
	 */
	
	namespace system\lib;
	
	
	use Yii;
	use yii\base\Component;
	use function json_encode;
	use function str_replace;
	use function urlencode;
	use function var_dump;
	
	class arvanApi extends Component {
		private $token = '';
		private $url = 'https://napi.arvancloud.com/ecc/v1/regions/{region}/{action}{extra}';
		/**
		 * CURL object
		 *
		 * @var
		 */
		protected $curl;
		const REGION_NEDERLAND_AMESTERDAM = 'nl-ams-su1';
		const REGION_IRAN_MOBINNET = 'ir-thr-mn1';
		const REGION_IRAN_ASIATEK = 'ir-thr-at1';
		
		const METHOD_POST = 'POST';
		const METHOD_GET = 'GET';
		const METHOD_PATCH = 'PATCH';
		const METHOD_DELETE = 'DELETE';
		
		/**
		 * Constructor
		 *
		 * @param string      $token        Telegram Bot API token
		 * @param string|null $trackerToken Yandex AppMetrica application api_key
		 */
		public function __construct( $token = null , $trackerToken = null ) {
			if ( ! empty( $token ) ) {
				$this->token = $token;
			}
			if ( empty( $this->token ) ) {
				$this->token =Yii::$app->Options->arvanCloud_token;
			}
			$this->curl = curl_init();
		}
		
		private function generateUrl( string $region , string $action , array $extra = [] ) {
			$url=$this->url;
			if (empty($region) && empty( $action)){
				$url = str_replace(  '/{region}/'   ,  ''  , $url );
			}
			if ( empty( $extra ) ) {
				$url = str_replace( [ '{region}' , '{action}' , '{extra}' ] , [ $region , $action , '' ] , $url );
			} else {
				$extraText = '';
				foreach ( $extra as $item ) {
					$extraText .= '/' . $item;
				}
				$url = str_replace(
					[ '{region}' , '{action}' , '{extra}' ] ,
					[ $region , $action , $extraText ] ,
					$url
				);
				
				
			}
			
			return $url;
		}
		
		public function execute( string $region , string $action , array $extra = [] , string $method = 'GET' , array $data = [] ) {
			
			$options =
				[
					CURLOPT_URL            => $this->generateUrl( $region , $action , $extra ) ,
					CURLOPT_RETURNTRANSFER => true ,
//					CURLOPT_POST           => null ,
//					CURLOPT_POSTFIELDS     => null ,
					CURLOPT_CUSTOMREQUEST  => $method ,
					CURLOPT_HTTPHEADER     =>
						[
							'Content-Type:application/x-www-form-urlencoded' ,
							'Authorization: ' . $this->token
						]
				];
			
			switch ( $method ) {
				case self::METHOD_PATCH:
				case self::METHOD_POST:
					$options[ CURLOPT_POST ]       = true;
					$options[ CURLOPT_POSTFIELDS ] = http_build_query($data);
					break;
			}
			
			
			curl_setopt_array( $this->curl , $options );
			
			$result = curl_exec( $this->curl );
			$result = json_decode( $result );
			
			return $result;
		}
		
		public function floatIp_list( string $region ) {
			return $this->execute( $region , 'float-ips' );
		}
		
		public function floatIp_create( string $region , string $description = '' ) {
			return $this->execute( $region , 'float-ips' , [] , self::METHOD_POST , [ 'description' => $description ] );
		}
		
		public function floatIp_delete( string $region , string $ipId ) {
			return $this->execute( $region , 'float-ips' , [ $ipId ] , self::METHOD_DELETE );
		}
		
		public function floatIp_attach( string $region , string $ipId , string $serverId , string $subnet_id , string $port_id ) {
			return $this->execute(
				$region ,
				'float-ips' ,
				[ $ipId , 'attach' ] ,
				self::METHOD_PATCH
				,
				[
					'server_id' => $serverId ,
					'subnet_id' => $subnet_id ,
					'port_id'   => $port_id
				]
			);
		}
		
		public function floatIp_detach( string $region , string $port_id ) {
			
			return $this->execute(
				$region ,
				'float-ips' ,
				[ 'detach' ] ,
				self::METHOD_PATCH
				,
				[
					'port_id' => $port_id
				]
			);
		}
		
		public function images_list( string $region ) {
			return $this->execute( $region , 'images' );
		}
		
		public function networks_list( string $region ) {
			return $this->execute( $region , 'networks' );
		}
		
		public function networks_subnet_details( string $region , string $subntId ) {
			return $this->execute( $region , 'subnets' , [ $subntId ] );
		}
		
		public function networks_subnet_delete( string $region , string $subntId ) {
			return $this->execute( $region , 'subnets' , [ $subntId ] , self::METHOD_DELETE );
		}
		
		public function networks_subnet_update(
			string $region ,
			string $subnetId ,
			string $name ,
			string $subnet_ip ,
			bool $enable_gateway ,
			string $subnet_gateway ,
			string $dhcp ,
			string $dns_servers ,
			string $dns_routs
		) {
			return $this->execute(
				$region ,
				'subnets' ,
				[ $subnetId ] ,
				self::METHOD_PATCH ,
				[
					'name'           => $name ,
					'subnet_ip'      => $subnet_ip ,
					'enable_gateway' => $enable_gateway ,
					'subnet_gateway' => $subnet_gateway ,
					'dhcp'           => $dhcp ,
					'dns_servers'    => $dns_servers ,
					'dns_routes'     => $dns_routs ,
				]
			);
		}
		
		public function networks_subnet_create(
			string $region ,
			string $subnetId ,
			string $name ,
			string $subnet_ip ,
			bool $enable_gateway ,
			string $subnet_gateway ,
			string $dhcp ,
			string $dns_servers ,
			string $dns_routs
		) {
			return $this->execute(
				$region ,
				'subnets' ,
				[ $subnetId ] ,
				self::METHOD_POST ,
				[
					'name'           => $name ,
					'subnet_ip'      => $subnet_ip ,
					'enable_gateway' => $enable_gateway ,
					'subnet_gateway' => $subnet_gateway ,
					'dhcp'           => $dhcp ,
					'dns_servers'    => $dns_servers ,
					'dns_routes'     => $dns_routs ,
				]
			);
		}
		
		public function networks_attach_to_server( string $region , string $networkId , string $serverId ) {
			return $this->execute(
				$region ,
				'networks' ,
				[ $networkId , 'attach' ] ,
				self::METHOD_PATCH
				,
				[
					'server_id' => $serverId ,
				]
			);
		}
		
		public function networks_detach_from_server( string $region , string $networkId , string $serverId ) {
			return $this->execute(
				$region ,
				'networks' ,
				[ $networkId , 'detach' ] ,
				self::METHOD_PATCH
				,
				[
					'server_id' => $serverId ,
				]
			);
		}
		
		public function server_actions_list( string $region , string $serverId ) {
			return $this->execute( $region , 'servers' , [ $serverId , 'actions' ] );
		}
		
		public function server_actions_rename( string $region , string $serverId , string $newName ) {
			return $this->execute(
				$region ,
				'servers' ,
				[ $serverId , 'rename' ] ,
				self::METHOD_POST ,
				[ 'name' => $newName ]
			);
		}
		
		public function server_actions_powerOff( string $region , string $serverId ) {
			return $this->execute( $region , 'servers' , [ $serverId , 'power-off' ] , self::METHOD_POST );
		}
		
		public function server_actions_powerOn( string $region , string $serverId ) {
			return $this->execute( $region , 'servers' , [ $serverId , 'power-on' ] , self::METHOD_POST );
		}
		
		public function server_actions_reboot( string $region , string $serverId ) {
			return $this->execute( $region , 'servers' , [ $serverId , 'reboot' ] , self::METHOD_POST );
		}
		
		public function server_actions_hardReboot( string $region , string $serverId ) {
			return $this->execute( $region , 'servers' , [ $serverId , 'hard-reboot' ] , self::METHOD_POST );
		}
		
		public function server_actions_rebuild( string $region , string $serverId , string $imageId ) {
			return $this->execute(
				$region ,
				'servers' ,
				[ $serverId , 'rebuild' ] ,
				self::METHOD_POST ,
				[ 'image_id' => $imageId ]
			);
		}
		
		public function server_actions_resize( string $region , string $serverId , string $flavorId ) {
			return $this->execute(
				$region ,
				'servers' ,
				[ $serverId , 'resize' ] ,
				self::METHOD_POST ,
				[ 'flavor_id' => $flavorId ]
			);
		}
		
		public function server_actions_snapshot( string $region , string $serverId , string $name ) {
			return $this->execute(
				$region ,
				'servers' ,
				[ $serverId , 'snapshot' ] ,
				self::METHOD_POST ,
				[ 'name' => $name ]
			);
		}
		
		public function server_actions_addSecurityGroup( string $region , string $serverId , string $securityGroupID ) {
			return $this->execute(
				$region ,
				'servers' ,
				[ $serverId , 'add-security-group' ] ,
				self::METHOD_POST ,
				[ 'security_group_id' => $securityGroupID ]
			);
		}
		
		public function server_actions_removeSecurityGroup( string $region , string $serverId , string $securityGroupID ) {
			return $this->execute(
				$region ,
				'servers' ,
				[ $serverId , 'remove-security-group' ] ,
				self::METHOD_POST ,
				[ 'security_group_id' => $securityGroupID ]
			);
		}
		
		public function server_actions_vnc( string $region , string $serverId ) {
			return $this->execute(
				$region ,
				'servers' ,
				[ $serverId , 'vnc' ] ,
				self::METHOD_GET
			);
		}
		
		public function server_list( string $region ) {
			return $this->execute(
				$region ,
				'servers' ,
				[],
				self::METHOD_GET
			);
		}
		
		public function server_new( string $region , string $name , string $key_name , string $network_id , string $flavor_id , string $image_id , array $security_groups , bool $ssh_key , int $count ) {
			return $this->execute(
				$region ,
				'servers' ,
				self::METHOD_POST ,
				[
					'name'            => $name ,
					'key_name'        => $key_name ,
					'network_id'      => $network_id ,
					'flavor_id'       => $flavor_id ,
					'image_id'        => $image_id ,
					'security_groups' => $security_groups ,
					'ssh_key'         => $ssh_key ,
					'count'           => $count
				]
			);
		}
		
		public function server_options( string $region ) {
			return $this->execute(
				$region ,
				'options' ,
				self::METHOD_GET
			);
		}
		
		public function server_details( string $region , string $serverId ) {
			return $this->execute(
				$region ,
				'servers' ,
				[ $serverId ] ,
				self::METHOD_GET
			);
		}
		
		public function server_delete( string $region , string $serverId ) {
			return $this->execute(
				$region ,
				'servers' ,
				[ $serverId ] ,
				self::METHOD_DELETE
			);
		}
		
		public function sizes( string $region ) {
			return $this->execute(
				$region ,
				'sizes' ,
				self::METHOD_GET
			);
		}
		
		public function quota( string $region ) {
			return $this->execute(
				$region ,
				'quota' ,
				self::METHOD_GET
			);
		}
		
		public function regions() {
			return $this->execute(
				'' ,
				'' ,
				[],
				self::METHOD_GET
			);
		}
		
		public function reports( string $region ) {
			return $this->execute(
				$region ,
				'reports' ,
				self::METHOD_GET
			);
		}
		
		public function ssh_keys_list( string $region ) {
			return $this->execute(
				$region ,
				'ssh-keys' ,
				self::METHOD_GET
			);
		}
		
		public function ssh_keys_create( string $region , string $name , string $public_key ) {
			return $this->execute(
				$region ,
				'ssh-keys' ,
				[] ,
				[
					'name'       => $name ,
					'public_key' => $public_key
				] ,
				self::METHOD_POST
			);
		}
		
		public function ssh_keys_delete( string $region , string $name ) {
			return $this->execute(
				$region ,
				'ssh-keys' ,
				[ $name ] ,
				self::METHOD_DELETE
			);
		}
		
		public function securityGroup_list( string $region ) {
			return $this->execute( $region , 'securities' , [] , self::METHOD_GET );
		}
		
		public function securityGroup_create( string $region , string $name , string $description = '' ) {
			return $this->execute(
				$region ,
				'securities' ,
				[] ,
				self::METHOD_POST ,
				[
					'name'        => $name ,
					'description' => $description
				]
			);
		}
		
		public function securityGroup_delete( string $region , string $securityGroupID ) {
			return $this->execute(
				$region ,
				'securities' ,
				[ $securityGroupID ] ,
				self::METHOD_DELETE
			);
		}
		
		public function securityGroup_getRules( string $region , string $securityGroupID ) {
			return $this->execute(
				$region ,
				'securities' ,
				[ 'security-rules' , $securityGroupID ] ,
				self::METHOD_GET
			);
		}
		
		public function securityGroup_newRule( string $region , string $securityGroupID , string $direction , string $portForm , string $portTo , string $protocol , array $ips = array() , string $description ) {
			return $this->execute(
				$region ,
				'securities' ,
				[ 'security-rules' , $securityGroupID ] ,
				self::METHOD_POST ,
				[
					'direction'   => $direction ,
					'port-form'   => $portForm ,
					'portTo'      => $portTo ,
					'protocol'    => $protocol ,
					'ips'         => $ips ,
					'description' => $description
				]
			);
		}
		
		public function securityGroup_deleteRule( string $region , string $securityGroupID ) {
			return $this->execute(
				$region ,
				'securities' ,
				[ 'security-rules' , $securityGroupID ] ,
				self::METHOD_DELETE
			);
		}
		
	}
