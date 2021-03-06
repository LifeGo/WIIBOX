<?php
/**
 * 文件上传
 *
 */
class CFileUpload extends CApplicationComponents 
{
	/**从$_FILES中取出上传数据的键名*/
	private $_fileUploadName = "";
	/**文件上传位置的固定部份*/
	private $_fileAbsPath = "";
	/**文件上传位置的可变部份*/
	private $_fileSavePath = "";
	/**设置上传文件的大小的最大值（单位为byte）*/
	private $_intMaxSize = 0;
	/**允许上传的文件类型*/
	private $_aryAllowedExtension = array();
	/**文件名允许的字符*/
	private $_strAllowedFileName = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';
	/**允许的文件名最大长度*/
	private $_intMaxFileName = 100;
	/**$_FILES错误定义*/
	private $_aryUploadError = array();
	
	public function init()
	{
		parent::init();
		$this -> _aryAllowedExtension = array(
				0=>CUtil::i18n('framework,cfileUpload_uploadError0'),
				1=>CUtil::i18n('framework,cfileUpload_uploadError1'),
				2=>CUtil::i18n('framework,cfileUpload_uploadError2'),
				3=>CUtil::i18n('framework,cfileUpload_uploadError3'),
				4=>CUtil::i18n('framework,cfileUpload_uploadError4'),
				6=>CUtil::i18n('framework,cfileUpload_uploadError5'),
		);
	}
	/**
	 * 设置从$_FILES中取出上传数据的键名
	 *
	 * @param string $_strFileUploadName	上传的键名
	 * @return CFileUpload
	 */
	public function setFileUploadName( $_strFileUploadName = "" )
	{
		$this->_fileUploadName = $_strFileUploadName;
		return $this;
	}
	
	/**
	 * 设置文件上传后的保存位置
	 *
	 * @param string $_strFileSavePath
	 * @return CFileUpload
	 */
	public function setFileSavePath( $_strFileSavePath = "" )
	{
		$this->_fileSavePath = $_strFileSavePath;
		return $this;
	}
	
	/**
	 * 设置文件上传的大小限制
	 *
	 * @param int $_intMaxSize	单位字节
	 * @return CFileUpload
	 */
	public function setMaxSize( $_intMaxSize = 0 )
	{
		$this->_intMaxSize = $_intMaxSize;
		return $this;
	}
	
	/**
	 * 设置允许上传的文件类型
	 *
	 * @param array $_aryAllowedExtension 允许上传的文件类型
	 * @return CFileUpload
	 */
	public function setAllowedExtension( $_aryAllowedExtension = array() )
	{
		$this->_aryAllowedExtension = $_aryAllowedExtension;
		return $this;
	}
	
	/**
	 * 设置网站上传位置的固定路径部份
	 *
	 * @param string $_strFileAbsPath
	 * @return CFileUpload
	 */
	public function setFileAbsPath( $_strFileAbsPath = "" )
	{
		$this->_fileAbsPath = $_strFileAbsPath;
		return $this;
	}
	
	/**
	 * 上传
	 *
	 */
	
	public function upload()
	{
		//属性校验
		if( empty( $this->_fileUploadName ) )
			throw new CException( CUtil::i18n( 'exception,exec_set_notSet' ) );
		if( empty( $this->_intMaxSize ) )
			throw new CException( CUtil::i18n( 'exception,exec_file_noSetloadSize' ) );
		if( empty( $this->_aryAllowedExtension ) )
			throw new CException( CUtil::i18n( 'exception,exec_file_noSetUploadType' ) );
		if( empty( $this->_fileAbsPath ) )
			throw new CException( CUtil::i18n( 'exception,exec_file_noSetUploadFixedAds' ));
		if( empty( $this->_fileSavePath ) )
			throw new CException( CUtil::i18n( 'exception,exec_file_noSetUploadAds' ));
		
		//PHP上传环境大小校验
		$POST_MAX_SIZE = ini_get('post_max_size');
		$unit = strtoupper(substr($POST_MAX_SIZE, -1));
		$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));
		$intSysMaxsize = $multiplier*(int)$POST_MAX_SIZE;
		if((int)$_SERVER['CONTENT_LENGTH'] > $intSysMaxsize && $POST_MAX_SIZE)
			throw new CException( CUtil::i18n( 'exception,exec_file_sizeCannotExceed' ).$intSysMaxsize.$unit);
		if( !isset($_FILES[$this->_fileUploadName]) )
			throw new CException( CUtil::i18n( 'exception,exec_upload_FileNotFound' ).$_FILES['.$this->_fileUploadName.']);
		
		//上传错误
		$FILE = $_FILES[$this->_fileUploadName];
		if( isset($FILE["error"]) && $FILE["error"] != 0 )
			throw new CException( $this->_aryUploadError[$FILE['error']] );
		
		//临时文件不是一个合法的上传文件
		if( !isset($FILE["tmp_name"] ) || !@is_uploaded_file( $FILE["tmp_name"] ) )
			throw new CException( CUtil::i18n( 'exception,exec_file_fileIllegal' ) );
		
		if( !isset( $FILE['name'] ) )
			throw new CException( '$_FILE'.CUtil::i18n('exception,exec_sys_none').'name' );
		
		//校验上传文件大小
		$intFileSize = @filesize( $FILE["tmp_name"] );
		if( !$intFileSize || $intFileSize > $this->_intMaxSize )
			throw new CModelException( CUtil::i18n( 'exception,exec_file_sizeExceedMaxSize' ) );		
		if( $intFileSize <= 0 )
			throw new CModelException( CUtil::i18n('exception,exec_file_tooSmallFile'));
		
		//文件名校验
		/*$strFileName = preg_replace('/[^'.$this->_strAllowedFileName.']|\.+$/i', "", basename($FILE['name']));		
		if (strlen( $strFileName ) == 0 || strlen( $strFileName ) > $this->_intMaxFileName )
			throw new CModelException( "文件名长度太长了,不要超过{$this->_intMaxFileName}位" );*/
		
		//文件类型校验
		$aryPathInfo = pathinfo( $FILE['name'] );
		$aryPathInfo['extension'] = strtolower( trim( $aryPathInfo['extension'] ) );
		if( !in_array( $aryPathInfo['extension'] , $this->_aryAllowedExtension ) )
			throw new CModelException( CUtil::i18n('exception,exec_file_allowedTypes').implode( ',' , $this->_aryAllowedExtension ) );
		
		//得到完整的文件保存路径【拼接文件扩展名】
		$this->_fileSavePath .= ".{$aryPathInfo['extension']}";
		$savefilepath = "{$this->_fileAbsPath}{$this->_fileSavePath}";
		
		//创建目录		
		$arySavePathInfo = pathinfo( $savefilepath );
		if( !file_exists( $arySavePathInfo['dirname'] ) && !@mkdir( $arySavePathInfo['dirname'] , 0755 , true ) )
			throw new CException( CUtil::i18n( 'exception,exec_file_unTomkdir' ).$arySavePathInfo['dirname'] );
		//保存文件到指定位置
		if( !@move_uploaded_file( $FILE["tmp_name"] , $savefilepath ) )
			throw new CModelException( CUtil::i18n( 'exception,exec_upload_Failed' ));
		
		$aryReturn = array();
		$aryReturn['pathr'] = $this->_fileSavePath;
		$aryReturn['patha'] = $savefilepath;
		$aryReturn['extension'] = $aryPathInfo['extension'];
		return $aryReturn;;
	}
//end class	
}
