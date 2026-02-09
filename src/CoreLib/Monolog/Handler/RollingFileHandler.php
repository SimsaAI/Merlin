<?php

namespace CoreLib\Monolog\Handler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class RollingFileHandler
 * @package CoreLib\Monolog\Handler
 */
class RollingFileHandler extends AbstractProcessingHandler
{

	/**
	 * @var array
	 */
	private $_options;

	/**
	 * @var bool
	 */
	private $_rotateSize;

	/**
	 * @var bool
	 */
	private $_rotateDate;

	/**
	 * @var string
	 */
	private $_fileName;

	/**
	 * @var int
	 */
	private $_fileTime;

	/**
	 * @var resource
	 */
	private $_fileHandle;

	/**
	 * @param string $name
	 * @param int $level
	 * @param bool $bubble
	 * @param array $options
	 */
	public function __construct($name, $level = Logger::DEBUG, $bubble = true, array $options = [])
	{
		$options += [
			'rollingMode' => 'composite',
			'staticLogFileName' => true,
			'maxFileSize' => 0,
			'maxSizeRollBackups' => -1,
			'datePattern' => '-Ymd',
			'maxAge' => 0,
			'compressGzip' => false,
			'skipEmpty' => false,
			'fileMode' => 0,
			'skipRotate' => false,
			'rotateOnce' => false,
		];
		$options['logFileName'] = $name;
		$options['rotateOnce'] = ($options['rollingMode'] == 'once');
		switch ($options['rollingMode']) {
			case 'composite':
				$this->_rotateSize = $options['maxFileSize'] > 0;
				$this->_rotateDate = !empty($options['datePattern']);
				break;
			case 'size':
				$this->_rotateSize = $options['maxFileSize'] > 0;
				break;
			case 'date':
				$this->_rotateDate = true;
				break;
		}
		$this->_options = $options;
		parent::__construct($level, $bubble);
		register_shutdown_function([$this, 'close']);
		// Test..
		//touch($this->getFilename(new \DateTime()), time() - 86400);
	}

	/**
	 * @param \DateTime $dateTime
	 * @param bool $current
	 * @return string
	 */
	private function getFilename($dateTime, $current = true)
	{
		$filename = $this->_options['logFileName'];
		if (!$current || !$this->_options['staticLogFileName']) {
			if ($this->_rotateSize) {
				$filename .= '.1';
			} elseif ($this->_rotateDate) {
				$filename .= $dateTime->format($this->_options['datePattern']);
			}
		}
		if (!$current && $this->_options['compressGzip']) {
			$filename .= '.gz';
		}
		return $filename;
	}

	public function close(): void
	{
		$this->closeLogFile();
	}

	private function openLogFile($mode)
	{
		$created = !file_exists($this->_fileName);
		$this->_fileHandle = fopen($this->_fileName, $mode);
		@flock($this->_fileHandle, LOCK_EX);
		if ($created && !empty($this->_options['fileMode'])) {
			@chmod($this->_fileName, $this->_options['fileMode']);
		}
	}

	private function closeLogFile()
	{
		if ($this->_fileHandle != null) {
			@flock($this->_fileHandle, LOCK_UN);
			fclose($this->_fileHandle);
			$this->_fileHandle = null;
		}
	}

	private function truncateLogFile()
	{
		$this->closeLogFile();
		$this->openLogFile('w+');
	}

	protected function write(array $record): void
	{
		$datetime = $record['datetime'];
		$this->_fileName = $this->getFilename($datetime, true);
		$this->openLogFile('a+');
		$this->_fileTime = filemtime($this->_fileName);
		if (!$this->_options['skipRotate']) {
			$this->rotate($record['datetime']);
			if ($this->_options['rotateOnce']) {
				$this->_options['skipRotate'] = true;
			}
		}
		fwrite($this->_fileHandle, (string) $record['formatted']);
		if ($this->_options['skipRotate']) {
			touch($this->_fileName, $this->_fileTime);
		}
		$this->closeLogFile();
	}

	/**
	 * @param \DateTime $dateTime
	 */
	private function rotate($dateTime)
	{
		if ($this->_rotateSize) {
			$this->rotateSize($dateTime);
			return;
		}
		if ($this->_rotateDate) {
			$this->rotateDate($dateTime);
			return;
		}
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	private function rotateSize($dateTime)
	{
		$fileSize = filesize($this->_fileName);
		if (empty($fileSize) && $this->_options['skipEmpty']) {
			return false;
		}
		if ($fileSize < $this->_options['maxFileSize']) {
			return false;
		}
		$baseLength = strlen($this->_options['logFileName']);
		$count = 0;
		$max = abs($this->_options['maxSizeRollBackups']);
		$rename = [];
		foreach (glob($this->_options['logFileName'] . '*') as $filename) {
			if ($filename == $this->_fileName) {
				continue;
			}
			$appendix = substr($filename, $baseLength);
			if (preg_match('/^\.(\d+)(.*)/', $appendix, $match)) {
				if (empty($match[2]) || substr_compare($match[2], '.', 0, 1) == 0) {
					$count++;
					if ($count >= $max) {
						unlink($filename);
					} else {
						$rename[$filename] = $this->_options['logFileName'] . '.' . ($count + 1) . $match[2];
					}
				}
			}
		}
		foreach (array_reverse($rename) as $filename => $newName) {
			rename($filename, $newName);
		}
		$fileDateTime = new \DateTime('@' . $this->_fileTime);
		$fileDateTime->setTimezone($dateTime->getTimezone());
		$backupFile = $this->getFilename($this->_options['logFileName'], false);
		if ($this->_options['compressGzip']) {
			fseek($this->_fileHandle, 0, SEEK_SET);
			$fileWrite = gzopen($backupFile, 'ab9');
			while (!feof($this->_fileHandle)) {
				gzwrite($fileWrite, fread($this->_fileHandle, 1048576));
			}
			gzclose($fileWrite);
			touch($backupFile, $this->_fileTime);
			if (!empty($this->_options['fileMode'])) {
				@chmod($backupFile, $this->_options['fileMode']);
			}
			$this->truncateLogFile();
		} elseif ($this->_options['staticLogFileName']) {
			fseek($this->_fileHandle, 0, SEEK_SET);
			$fileWrite = fopen($backupFile, 'a');
			while (!feof($this->_fileHandle)) {
				fwrite($fileWrite, fread($this->_fileHandle, 1048576));
			}
			fclose($fileWrite);
			touch($backupFile, $this->_fileTime);
			if (!empty($this->_options['fileMode'])) {
				@chmod($backupFile, $this->_options['fileMode']);
			}
			$this->truncateLogFile();
		} elseif (!$this->_options['staticLogFileName']) {
			$this->truncateLogFile();
		}
		return true;
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	private function rotateDate($dateTime)
	{
		$fileDateTime = new \DateTime('@' . $this->_fileTime);
		$fileDateTime->setTimezone($dateTime->getTimezone());
		//var_dump(basename($this->_fileName) . ' cmp ' . $dateTime->format('Ymd') . ' <= ' . $fileDateTime->format('Ymd'));
		if ($dateTime->format('Ymd') <= $fileDateTime->format('Ymd')) {
			return false;
		}
		$backupFile = $this->getFilename($fileDateTime, false);
		if (filesize($this->_fileName) > 0 || !$this->_options['skipEmpty']) {
			if ($this->_options['compressGzip']) {
				fseek($this->_fileHandle, 0, SEEK_SET);
				$fileWrite = gzopen($backupFile, 'ab9');
				while (!feof($this->_fileHandle)) {
					gzwrite($fileWrite, fread($this->_fileHandle, 1048576));
				}
				gzclose($fileWrite);
				touch($backupFile, $this->_fileTime);
				if (!empty($this->_options['fileMode'])) {
					@chmod($backupFile, $this->_options['fileMode']);
				}
				$this->truncateLogFile();
			} elseif ($this->_options['staticLogFileName']) {
				fseek($this->_fileHandle, 0, SEEK_SET);
				$fileWrite = fopen($backupFile, 'a');
				while (!feof($this->_fileHandle)) {
					fwrite($fileWrite, fread($this->_fileHandle, 1048576));
				}
				fclose($fileWrite);
				touch($backupFile, $this->_fileTime);
				if (!empty($this->_options['fileMode'])) {
					@chmod($backupFile, $this->_options['fileMode']);
				}
				$this->truncateLogFile();
			}
		} elseif (!$this->_options['staticLogFileName']) {
			$this->truncateLogFile();
		}
		if ($this->_options['maxAge'] > 0) {
			$expired = $dateTime->getTimestamp() - $this->_options['maxAge'] * 86400;
			foreach (glob($this->_options['logFileName'] . '*') as $filename) {
				if ($this->_fileName != $filename && filemtime($filename) <= $expired) {
					unlink($filename);
				}
			}
		}
		return true;
	}
}
