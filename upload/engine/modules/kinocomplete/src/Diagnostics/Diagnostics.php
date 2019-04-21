<?php

namespace Kinocomplete\Diagnostics;

use Kinocomplete\Api\HdvbApi;
use Kinocomplete\Api\TmdbApi;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Api\MoonwalkApi;
use Kinocomplete\Service\DefaultService;
use Kinocomplete\Container\ContainerFactory;
use GuzzleHttp\Exception\BadResponseException;
use Kinocomplete\Exception\InvalidTokenException;
use Kinocomplete\Exception\HostNotFoundException;
use Kinocomplete\Exception\TokenNotFoundException;
use Kinocomplete\Exception\BasePathNotFoundException;
use Kinocomplete\Exception\LanguageNotFoundException;

class Diagnostics extends DefaultService
{
  /**
   * System version check.
   *
   * @return bool
   */
  public function systemVersionCheck()
  {
    $version    = $this->container->get('system')->get('version_id');
    $versionMin = $this->container->get('system_version_min');
    $versionMax = $this->container->get('system_version_max');

    if ($versionMax[0] === '^') {

      $versionMax = substr($versionMax, 1);
      $versionMax = floor($versionMax) +1;

      if ($version >= $versionMin && $version < $versionMax)
        return true;

    } else {

      if ($version >= $versionMin && $version <= $versionMax)
        return true;
    }

    die("Версия DataLife Engine $version не поддерживается.");
  }

  /**
   * Get requirements errors.
   *
   * @return array
   * @throws \Exception
   */
  public function getRequirementsErrors()
  {
    $messages = [];

    /** @var SystemApi $systemApi */
    $systemApi = $this->container->get('system_api');

    // PHP version.
    if (version_compare(phpversion(), '5.6.0', '<'))
      $messages[] = 'Требуется версия PHP не ниже 5.6.0';

    return $messages;
  }

  /**
   * Get file system errors.
   *
   * @return array
   */
  public function getFileSystemErrors()
  {
    $messages = [];

    // Checking access rights to the cache folder.
    $systemCacheDir = $this->container->get('system_cache_dir');

    if (
      !is_readable($systemCacheDir) ||
      !is_writable($systemCacheDir)
    ) $messages[] = 'Директория временных файлов недоступна.';

    // Checking access rights to the upload folder.
    $systemUploadDir = $this->container->get('system_upload_dir');

    if (
      !is_readable($systemUploadDir) ||
      !is_writable($systemUploadDir)
    ) $messages[] = 'Директория загрузок недоступна.';

    return $messages;
  }

  /**
   * Get proxy errors.
   *
   * @return array
   */
  public function getProxyErrors()
  {
    $messages = [];

    $proxyAddress = $this->container->get('proxy_address');

    if (!$proxyAddress)
      $messages[] = 'Адрес прокси-сервера не назначен.';

    return $messages;
  }

  /**
   * Get moonwalk errors.
   *
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getMoonwalkErrors()
  {
    $messages = [];

    /** @var MoonwalkApi $moonwalkApi */
    $moonwalkApi = $this->container->get('moonwalk_api');

    try {

      $moonwalkApi->accessChecking(true);

    } catch (HostNotFoundException $exception) {

      $messages[] = 'В настройках модуля требуется указать имя сервера Moonwalk.';

    } catch (TokenNotFoundException $exception) {

      $messages[] = 'В настройках модуля требуется указать Moonwalk Token.';

    } catch (BasePathNotFoundException $exception) {

      $messages[] = 'В настройках модуля требуется указать базовый путь сервера Moonwalk.';

    } catch (InvalidTokenException $exception) {

      $messages[] = 'В настройках модуля указан неверный Moonwalk Token.';

    } catch (\Exception $exception) {

      $reasonPhrase = $exception instanceof BadResponseException
        ? $exception->getResponse()->getReasonPhrase()
        : $exception->getMessage();

      $messages[] = sprintf(
        'Ошибка при проверке доступа к Moonwalk: %s',
        $reasonPhrase
      );
    }

    return $messages;
  }

  /**
   * Get tmdb errors.
   *
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getTmdbErrors()
  {
    $messages = [];

    /** @var TmdbApi $tmdbApi */
    $tmdbApi = $this->container->get('tmdb_api');

    try {

      $tmdbApi->accessChecking(true);

    } catch (HostNotFoundException $exception) {

      $messages[] = 'В настройках модуля требуется указать имя сервера TMDB.';

    } catch (TokenNotFoundException $exception) {

      $messages[] = 'В настройках модуля требуется указать TMDB Token.';

    } catch (BasePathNotFoundException $exception) {

      $messages[] = 'В настройках модуля требуется указать базовый путь сервера TMDB.';

    } catch (LanguageNotFoundException $exception) {

      $messages[] = 'В настройках модуля не указан язык TMDB.';

    } catch (InvalidTokenException $exception) {

      $messages[] = 'В настройках модуля указан неверный TMDB Token.';

    } catch (\Exception $exception) {

      $reasonPhrase = $exception instanceof BadResponseException
        ? $exception->getResponse()->getReasonPhrase()
        : $exception->getMessage();

      $messages[] = sprintf(
        'Ошибка при проверке доступа к TMDB: %s',
        $reasonPhrase
      );
    }

    return $messages;
  }

  /**
   * Get hdvb errors.
   *
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getHdvbErrors()
  {
    $messages = [];

    /** @var HdvbApi $hdvbApi */
    $hdvbApi = $this->container->get('hdvb_api');

    try {

      $hdvbApi->accessChecking(true);

    } catch (HostNotFoundException $exception) {

      $messages[] = 'В настройках модуля требуется указать имя сервера HDVB.';

    } catch (TokenNotFoundException $exception) {

      $messages[] = 'В настройках модуля требуется указать HDVB Token.';

    } catch (BasePathNotFoundException $exception) {

      $messages[] = 'В настройках модуля требуется указать базовый путь сервера HDVB.';

    } catch (InvalidTokenException $exception) {

      $messages[] = 'В настройках модуля указан неверный HDVB Token.';

    } catch (\Exception $exception) {

      $reasonPhrase = $exception instanceof BadResponseException
        ? $exception->getResponse()->getReasonPhrase()
        : $exception->getMessage();

      $messages[] = sprintf(
        'Ошибка при проверке доступа к HDVB: %s',
        $reasonPhrase
      );
    }

    return $messages;
  }

  /**
   * Get errors.
   *
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getErrors()
  {
    $moonwalkEnabled = $this->container->get('moonwalk_enabled');
    $tmdbEnabled     = $this->container->get('tmdb_enabled');
    $hdvbEnabled     = $this->container->get('hdvb_enabled');
    $rutorEnabled    = $this->container->get('rutor_enabled');
    $proxyEnabled    = $this->container->get('proxy_enabled');

    $messages = [];

    // Requirements.
    $messages += $this->getRequirementsErrors();

    // File system.
    $messages += $this->getFileSystemErrors();

    // Sources enabling.
    if (
      !$moonwalkEnabled &&
      !$tmdbEnabled &&
      !$hdvbEnabled &&
      !$rutorEnabled
    ) $messages[] = 'Включите хотя бы один источник данных.';

    // Moonwalk settings.
    if ($moonwalkEnabled)
      $messages += $this->getMoonwalkErrors();

    // Tmdb settings.
    if ($tmdbEnabled)
      $messages += $this->getTmdbErrors();

    // Hdvb settings.
    if ($hdvbEnabled)
      $messages += $this->getHdvbErrors();

    // Proxy settings.
    if ($proxyEnabled)
      $messages += $this->getProxyErrors();

    return $messages;
  }

  /**
   * Get warnings.
   *
   * @return array
   */
  public function getWarnings()
  {
    $messages = [];

    $videoFields = ContainerFactory::fromNamespace(
      $this->container,
      'video_field',
      true
    );

    $videoFieldsConfigured = false;

    foreach ($videoFields as $extraField) {

      if ($extraField) {
        $videoFieldsConfigured = true;
        break;
      }
    }

    if (!$videoFieldsConfigured)
      $messages[] = 'Дополнительные поля не настроены.';

    return $messages;
  }
}
