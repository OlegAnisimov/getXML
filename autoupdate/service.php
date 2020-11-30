<?php

	use UmiCms\Service;

	/** Класс служебного функционала */
	class AutoUpdateService {

		/** @var autoupdate $module */
		public $module;

		/**
		 * Выводит в буффер служебную информацию о системе
		 * @throws publicException
		 * @throws coreException
		 * @throws Exception
		 */
		public function service() {
			$event = mb_strtoupper(getRequest('param0'));
			$module = $this->module;

			switch ($event) {
				case 'STATUS': {
					$result = Service::RegistrySettings()->getStatus();
					break;
				}
				case 'VERSION': {
					$result = $module->getVersion() . PHP_EOL . $module->getRevision();
					break;
				}
				case 'LAST_UPDATED': {
					$result = $module->getUpdateTime();
					break;
				}
				case 'MODULES': {
					$result = $this->getModules();
					break;
				}
				case 'EXTENSIONS': {
					$result = $this->getExtensionList();
					break;
				}
				case 'DOMAINS': {
					$result = $this->getDomains();
					break;
				}
				case 'SUPPORT':
					$supportEndDate = $module->getSupportEndDate();
					$result = '';

					if (isset($supportEndDate['date']) && isset($supportEndDate['date']['@timestamp'])) {
						$result = date('d.m.Y H:i:s', $supportEndDate['date']['@timestamp']);
					}

					break;
				default: {
					$result = 'UNHANDLED_EVENT';
				}
			}

			$module->flush($result, 'text/plain');
		}

		/**
		 * Возвращает список модулей, которые не должны быть установлены на системе
		 * @return array
		 * @throws publicException
		 * @throws Exception
		 */
		public function getIllegalModules() {
			try {
				return $this->module->getUpdateServerClient()
					->getIllegalModuleList();
			} catch (RuntimeException $exception) {
				throw new publicException($exception->getMessage());
			}
		}

		/**
		 * Возвращает список расширений, которые не должны быть установлены на системе
		 * @return array
		 * @throws publicException
		 * @throws Exception
		 */
		public function getIllegalExtensionList() {
			try {
				return $this->module->getUpdateServerClient()
					->getIllegalExtensionList();
			} catch (RuntimeException $exception) {
				throw new publicException($exception->getMessage());
			}
		}

		/**
		 * Запускает удаление компонентов, которые не должны быть установлены
		 * @throws publicException
		 */
		public function deleteIllegalComponents() {
			$this->deleteIllegalModules();
			$this->deleteIllegalExtensions();
			// следующие действия пришлось поместить сюда из-за того, что installer переехал из smu в classes
			$this->deleteOldRootFiles();
			$this->switchOffOldConfigValues();
		}

		/**
		 * Запускает удаление модулей, которые не должны быть установлены
		 * @throws publicException
		 */
		public function deleteIllegalModules() {
			$registry = Service::Registry();

			foreach ($this->getIllegalModules() as $name) {
				$registry->delete("//modules/{$name}");
			}
		}

		/**
		 * Запускает удаление расширений, которые не должны быть установлены
		 * @throws publicException
		 */
		public function deleteIllegalExtensions() {
			$registry = Service::ExtensionRegistry();

			foreach ($this->getIllegalExtensionList() as $name) {
				$registry->delete($name);
			}
		}

		/** Удаляет старые корневые файлы */
		public function deleteOldRootFiles() {
			$fileList = [
				'./__install.php',
				'./autothumbs.php',
				'./captcha.php',
				'./composer.umi.json',
				'./composer.umi.lock',
				'./counter.php',
				'./cron.php',
				'./favicon.php',
				'./go-out.php',
				'./install.php',
				'./releaseStreams.php',
				'./sbots.php',
				'./session.php',
				'./sitemap.php',
				'./static_banner.php',
				'./tinyurl.php',
			];

			foreach ($fileList as $file) {
				if (is_file($file)) {
					unlink($file);
				}
			}
		}

		/** Отключает старые значения конфигурации */
		public function switchOffOldConfigValues() {
			$config = mainConfiguration::getInstance();
			$config->set('system', 'default-skin', 'modern');
			$config->set('system', 'skins', ['modern']);
			$config->set('includes', 'system.modules', '~/classes/components/');
			$config->set('includes', 'virtual-modules', '~/classes/components/');
			$config->set('includes', 'system.error', '~/styles/common/errors/');
			$config->set('includes', 'system.stub', '~/styles/common/stub/stub.html');
			$config->set('includes', 'system.templates.xsl', '~/styles/common/xsl/');
			$config->save();
		}

		/**
		 * Возвращает перечень установленных модулей
		 * @return string
		 */
		protected function getModules() {
			$result = '';

			foreach (Service::Registry()->getList('//modules') as $module) {
				$result .= array_shift($module) . PHP_EOL;
			}

			return $result;
		}

		/**
		 * Возвращает перечень доменов системы
		 * @return string
		 */
		protected function getDomains() {
			$result = '';

			/** @var iDomain $domain */
			foreach (Service::DomainCollection()->getList() as $domain) {
				$result .= $domain->getHost() . PHP_EOL;
			}

			return $result;
		}

		/**
		 * Возвращает список расширений
		 * @return string
		 */
		private function getExtensionList() {
			return implode(PHP_EOL, Service::ExtensionRegistry()->getList());
		}

		/** @deprecated */
		public function get_file($url) {
			return null;
		}
	}
