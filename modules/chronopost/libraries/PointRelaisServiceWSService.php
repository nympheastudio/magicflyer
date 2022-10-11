<?php
/**
  * MODULE PRESTASHOP OFFICIEL CHRONOPOST
  *
  * LICENSE : All rights reserved - COPY AND REDISTRIBUTION FORBIDDEN WITHOUT PRIOR CONSENT FROM OXILEO
  * LICENCE : Tous droits réservés, le droit d'auteur s'applique - COPIE ET REDISTRIBUTION INTERDITES
* SANS ACCORD EXPRES D'OXILEO
  *
  * @author    Oxileo SAS <contact@oxileo.eu>
  * @copyright 2001-2018 Oxileo SAS
  * @license   Proprietary - no redistribution without authorization
  */

class pointChronopost
{
    /** @var string */  public $adresse1;
  /** @var string */  public $adresse2;
  /** @var string */  public $adresse3;
  /** @var string */  public $codePostal;
  /** @var dateTime */  public $dateArriveColis;
  /** @var string */  public $horairesOuvertureDimanche;
  /** @var string */  public $horairesOuvertureJeudi;
  /** @var string */  public $horairesOuvertureLundi;
  /** @var string */  public $horairesOuvertureMardi;
  /** @var string */  public $horairesOuvertureMercredi;
  /** @var string */  public $horairesOuvertureSamedi;
  /** @var string */  public $horairesOuvertureVendredi;
  /** @var string */  public $horairesOuverturesFormates;
  /** @var string */  public $identifiantChronopost;
  /** @var string */  public $localite;
  /** @var string */  public $nomEnseigne;
  /** @var string */  public $typeDePoint;
}

class bureauDeTabac
{
    /** @var string */  public $adresse1;
  /** @var string */  public $adresse2;
  /** @var string */  public $adresse3;
  /** @var string */  public $codePostal;
  /** @var dateTime */  public $dateArriveColis;
  /** @var string */  public $horairesOuvertureDimanche;
  /** @var string */  public $horairesOuvertureJeudi;
  /** @var string */  public $horairesOuvertureLundi;
  /** @var string */  public $horairesOuvertureMardi;
  /** @var string */  public $horairesOuvertureMercredi;
  /** @var string */  public $horairesOuvertureSamedi;
  /** @var string */  public $horairesOuvertureVendredi;
  /** @var string */  public $identifiantChronopostPointA2PAS;
  /** @var string */  public $localite;
  /** @var string */  public $nomEnseigne;
}

class bureauDeTabacAvecCoord extends bureauDeTabac
{
    /** @var double */  public $coordGeoLatitude;
  /** @var double */  public $coordGeoLongitude;
  /** @var string */  public $urlGoogleMaps;
}

class bureauDeTabacAvecPF extends bureauDeTabacAvecCoord
{
    /** @var dateTime */  public $periodeDeFermeture1Debut;
  /** @var dateTime */  public $periodeDeFermeture1Fin;
  /** @var dateTime */  public $periodeDeFermeture2Debut;
  /** @var dateTime */  public $periodeDeFermeture2Fin;
  /** @var dateTime */  public $periodeDeFermeture3Debut;
  /** @var dateTime */  public $periodeDeFermeture3Fin;
}

class pointCHRResult
{
    /** @var int */ public $errorCode;
  /** @var string */  public $errorMessage;
  /** @var pointCHR */  public $listePointRelais;
  /** @var int */ public $qualiteReponse;
  /** @var string */  public $wsRequestId;
}

class pointCHR
{
    /** @var boolean */ public $accesPersonneMobiliteReduite;
  /** @var boolean */ public $actif;
  /** @var string */  public $adresse1;
  /** @var string */  public $adresse2;
  /** @var string */  public $adresse3;
  /** @var string */  public $codePays;
  /** @var string */  public $codePostal;
  /** @var string */  public $coordGeolocalisationLatitude;
  /** @var string */  public $coordGeolocalisationLongitude;
  /** @var int */ public $distanceEnMetre;
  /** @var string */  public $identifiant;
  /** @var string */  public $indiceDeLocalisation;
  /** @var listeHoraireOuverturePourUnJour */ public $listeHoraireOuverture;
  /** @var periodeFermeture */  public $listePeriodeFermeture;
  /** @var string */  public $localite;
  /** @var string */  public $nom;
  /** @var int */ public $poidsMaxi;
  /** @var string */  public $typeDePoint;
  /** @var string */  public $urlGoogleMaps;
}

class listeHoraireOuverturePourUnJour
{
    /** @var string */  public $horairesAsString;
  /** @var int */ public $jour;
  /** @var horaireOuverture */  public $listeHoraireOuverture;
}

class horaireOuverture
{
    /** @var string */  public $debut;
  /** @var string */  public $fin;
}

class periodeFermeture
{
    /** @var dateTime */  public $calendarDeDebut;
  /** @var dateTime */  public $calendarDeFin;
  /** @var int */ public $numero;
}

class tourneeResult
{
    /** @var int */ public $errorCode;
  /** @var string */  public $errorMessage;
  /** @var tournee */ public $tournee;
}

class tournee
{
    /** @var string */  public $code;
  /** @var boolean */ public $localise;
  /** @var string */  public $type;
}

class tourneeCompleteResult
{
    /** @var int */ public $errorCode;
  /** @var string */  public $errorMessage;
  /** @var tourneeComplete */ public $tourneeComplete;
}

class tourneeComplete extends tournee
{
    /** @var string */  public $codeTourneeMaitre;
  /** @var string */  public $codeTypeTournee;
  /** @var string */  public $coutMensuelBatiment;
  /** @var string */  public $cubage;
  /** @var string */  public $cubageDistri;
  /** @var string */  public $detailPrestation;
  /** @var string */  public $heureTheoriqueRetour;
  /** @var string */  public $idMoyenPropre;
  /** @var string */  public $idSecteur;
  /** @var string */  public $idSousTraitant;
  /** @var string */  public $idVehicule;
  /** @var string */  public $picking;
  /** @var string */  public $planDistri;
  /** @var string */  public $posteComptable;
  /** @var string */  public $pourcentageCoChargement;
  /** @var string */  public $qualification;
  /** @var string */  public $spot;
  /** @var string */  public $trigrammeAgence;
  /** @var string */  public $typeTournee;
  /** @var string */  public $zone;
}

class recherchePointChronopostParId
{
    /** @var string */  public $id;
}

class recherchePointChronopostParIdResponse
{
    /** @var pointChronopost */ public $return;
}

class rechercheBtAvecPFParIdChronopostA2Pas
{
    /** @var string */  public $id;
}

class rechercheBtAvecPFParIdChronopostA2PasResponse
{
    /** @var bureauDeTabacAvecPF */ public $return;
}

class rechercheBtParIdChronopostA2Pas
{
    /** @var string */  public $id;
}

class rechercheBtParIdChronopostA2PasResponse
{
    /** @var bureauDeTabacAvecCoord */  public $return;
}

class rechercheDetailPointChronopost
{
    /** @var string */  public $accountNumber;
  /** @var string */  public $password;
  /** @var string */  public $identifiant;
}

class rechercheDetailPointChronopostResponse
{
    /** @var pointCHRResult */  public $return;
}

class getAllChronopostAgences
{
}

class getAllChronopostAgencesResponse
{
    /** @var pointChronopost */ public $return;
}

class recherchePointChronopostInter
{
    /** @var string */  public $accountNumber;
  /** @var string */  public $password;
  /** @var string */  public $address;
  /** @var string */  public $zipCode;
  /** @var string */  public $city;
  /** @var string */  public $countryCode;
  /** @var string */  public $type;
  /** @var string */  public $productCode;
  /** @var string */  public $service;
  /** @var string */  public $weight;
  /** @var string */  public $shippingDate;
  /** @var string */  public $maxPointChronopost;
  /** @var string */  public $maxDistanceSearch;
  /** @var string */  public $holidayTolerant;
  /** @var string */  public $language;
  /** @var string */  public $version;
}

class recherchePointChronopostInterResponse
{
    /** @var pointCHRResult */  public $return;
}

class rechercheTournee
{
    /** @var string */  public $codeTournee;
}

class rechercheTourneeResponse
{
    /** @var tourneeResult */ public $return;
}

class recherchePointChronopostParCoordonneesGeographiques
{
    /** @var string */  public $accountNumber;
  /** @var string */  public $password;
  /** @var string */  public $coordGeoLatitude;
  /** @var string */  public $coordGeoLongitude;
  /** @var string */  public $type;
  /** @var string */  public $productCode;
  /** @var string */  public $service;
  /** @var string */  public $weight;
  /** @var string */  public $shippingDate;
  /** @var string */  public $maxPointChronopost;
  /** @var string */  public $maxDistanceSearch;
  /** @var string */  public $holidayTolerant;
}

class recherchePointChronopostParCoordonneesGeographiquesResponse
{
    /** @var pointCHRResult */  public $return;
}

class rechercheDetailPointChronopostInter
{
    /** @var string */  public $accountNumber;
  /** @var string */  public $password;
  /** @var string */  public $identifiant;
  /** @var string */  public $countryCode;
  /** @var string */  public $language;
  /** @var string */  public $version;
}

class rechercheDetailPointChronopostInterResponse
{
    /** @var pointCHRResult */  public $return;
}

class rechercheTourneeParTypeTourneeEtPosteComptable
{
    /** @var string */  public $codeTournee;
  /** @var string */  public $typeTournee;
  /** @var string */  public $posteComptable;
}

class rechercheTourneeParTypeTourneeEtPosteComptableResponse
{
    /** @var tourneeCompleteResult */ public $return;
}

class rechercheBtAvecPFParCodeproduitEtCodepostalEtDate
{
    /** @var string */  public $codeProduit;
  /** @var string */  public $codePostal;
  /** @var string */  public $date;
}

class rechercheBtAvecPFParCodeproduitEtCodepostalEtDateResponse
{
    /** @var bureauDeTabacAvecPF */ public $return;
}

class recherchePointChronopost
{
    /** @var string */  public $accountNumber;
  /** @var string */  public $password;
  /** @var string */  public $address;
  /** @var string */  public $zipCode;
  /** @var string */  public $city;
  /** @var string */  public $countryCode;
  /** @var string */  public $type;
  /** @var string */  public $productCode;
  /** @var string */  public $service;
  /** @var string */  public $weight;
  /** @var string */  public $shippingDate;
  /** @var string */  public $maxPointChronopost;
  /** @var string */  public $maxDistanceSearch;
  /** @var string */  public $holidayTolerant;
}

class recherchePointChronopostResponse
{
    /** @var pointCHRResult */  public $return;
}

class rechercheBtParCodeproduitEtCodepostalEtDate
{
    /** @var string */  public $codeProduit;
  /** @var string */  public $codePostal;
  /** @var string */  public $date;
}

class rechercheBtParCodeproduitEtCodepostalEtDateResponse
{
    /** @var bureauDeTabacAvecCoord */  public $return;
}


/**
 * PointRelaisServiceWSService class
 *
 *
 *
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class PointRelaisServiceWSService extends \SoapClient
{
    const WSDL_FILE = "https://www.chronopost.fr/recherchebt-ws-cxf/PointRelaisServiceWS?wsdl";
    private $classmap = array(
      'pointChronopost' => 'pointChronopost',
      'bureauDeTabac' => 'bureauDeTabac',
      'bureauDeTabacAvecCoord' => 'bureauDeTabacAvecCoord',
      'bureauDeTabacAvecPF' => 'bureauDeTabacAvecPF',
      'pointCHRResult' => 'pointCHRResult',
      'pointCHR' => 'pointCHR',
      'listeHoraireOuverturePourUnJour' => 'listeHoraireOuverturePourUnJour',
      'horaireOuverture' => 'horaireOuverture',
      'periodeFermeture' => 'periodeFermeture',
      'tourneeResult' => 'tourneeResult',
      'tournee' => 'tournee',
      'tourneeCompleteResult' => 'tourneeCompleteResult',
      'tourneeComplete' => 'tourneeComplete',
      'recherchePointChronopostParId' => 'recherchePointChronopostParId',
      'recherchePointChronopostParIdResponse' => 'recherchePointChronopostParIdResponse',
      'rechercheBtAvecPFParIdChronopostA2Pas' => 'rechercheBtAvecPFParIdChronopostA2Pas',
      'rechercheBtAvecPFParIdChronopostA2PasResponse' => 'rechercheBtAvecPFParIdChronopostA2PasResponse',
      'rechercheBtParIdChronopostA2Pas' => 'rechercheBtParIdChronopostA2Pas',
      'rechercheBtParIdChronopostA2PasResponse' => 'rechercheBtParIdChronopostA2PasResponse',
      'rechercheDetailPointChronopost' => 'rechercheDetailPointChronopost',
      'rechercheDetailPointChronopostResponse' => 'rechercheDetailPointChronopostResponse',
      'getAllChronopostAgences' => 'getAllChronopostAgences',
      'getAllChronopostAgencesResponse' => 'getAllChronopostAgencesResponse',
      'recherchePointChronopostInter' => 'recherchePointChronopostInter',
      'recherchePointChronopostInterResponse' => 'recherchePointChronopostInterResponse',
      'rechercheTournee' => 'rechercheTournee',
      'rechercheTourneeResponse' => 'rechercheTourneeResponse',
      'recherchePointChronopostParCoordonneesGeographiques' => 'recherchePointChronopostParCoordonneesGeographiques',
      'recherchePointChronopostParCoordonneesGeographiquesResponse' => 'recherchePointChronopostParCoordonneesGeographiquesResponse',
      'rechercheDetailPointChronopostInter' => 'rechercheDetailPointChronopostInter',
      'rechercheDetailPointChronopostInterResponse' => 'rechercheDetailPointChronopostInterResponse',
      'rechercheTourneeParTypeTourneeEtPosteComptable' => 'rechercheTourneeParTypeTourneeEtPosteComptable',
      'rechercheTourneeParTypeTourneeEtPosteComptableResponse' => 'rechercheTourneeParTypeTourneeEtPosteComptableResponse',
      'rechercheBtAvecPFParCodeproduitEtCodepostalEtDate' => 'rechercheBtAvecPFParCodeproduitEtCodepostalEtDate',
      'rechercheBtAvecPFParCodeproduitEtCodepostalEtDateResponse' => 'rechercheBtAvecPFParCodeproduitEtCodepostalEtDateResponse',
      'recherchePointChronopost' => 'recherchePointChronopost',
      'recherchePointChronopostResponse' => 'recherchePointChronopostResponse',
      'rechercheBtParCodeproduitEtCodepostalEtDate' => 'rechercheBtParCodeproduitEtCodepostalEtDate',
      'rechercheBtParCodeproduitEtCodepostalEtDateResponse' => 'rechercheBtParCodeproduitEtCodepostalEtDateResponse',
      );

    public function __construct($wsdl = null, $options = array())
    {
        foreach ($this->classmap as $key => $value) {
            if (!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }
        if (isset($options['headers'])) {
            $this->__setSoapHeaders($options['headers']);
        }
        parent::__construct($wsdl ?: self::WSDL_FILE, $options);
    }

  /**
   *
   *
   * @param recherchePointChronopostParId $parameters
   * @return recherchePointChronopostParIdResponse
   */
  public function recherchePointChronopostParId(recherchePointChronopostParId $parameters)
  {
      return $this->__soapCall('recherchePointChronopostParId', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }

  /**
   *
   *
   * @param rechercheBtAvecPFParIdChronopostA2Pas $parameters
   * @return rechercheBtAvecPFParIdChronopostA2PasResponse
   */
  public function rechercheBtAvecPFParIdChronopostA2Pas(rechercheBtAvecPFParIdChronopostA2Pas $parameters)
  {
      return $this->__soapCall('rechercheBtAvecPFParIdChronopostA2Pas', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }

  /**
   *
   *
   * @param rechercheBtParIdChronopostA2Pas $parameters
   * @return rechercheBtParIdChronopostA2PasResponse
   */
  public function rechercheBtParIdChronopostA2Pas(rechercheBtParIdChronopostA2Pas $parameters)
  {
      return $this->__soapCall('rechercheBtParIdChronopostA2Pas', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }

  /**
   *
   *
   * @param rechercheDetailPointChronopost $parameters
   * @return rechercheDetailPointChronopostResponse
   */
  public function rechercheDetailPointChronopost(rechercheDetailPointChronopost $parameters)
  {
      return $this->__soapCall('rechercheDetailPointChronopost', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }

  /**
   *
   *
   * @param getAllChronopostAgences $parameters
   * @return getAllChronopostAgencesResponse
   */
  public function getAllChronopostAgences(getAllChronopostAgences $parameters)
  {
      return $this->__soapCall('getAllChronopostAgences', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }

  /**
   *
   *
   * @param recherchePointChronopostInter $parameters
   * @return recherchePointChronopostInterResponse
   */
  public function recherchePointChronopostInter($parameters)
  {
      return $this->__soapCall('recherchePointChronopostInter', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }

  /**
   *
   *
   * @param rechercheTournee $parameters
   * @return rechercheTourneeResponse
   */
  public function rechercheTournee(rechercheTournee $parameters)
  {
      return $this->__soapCall('rechercheTournee', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }

  /**
   *
   *
   * @param recherchePointChronopostParCoordonneesGeographiques $parameters
   * @return recherchePointChronopostParCoordonneesGeographiquesResponse
   */
  public function recherchePointChronopostParCoordonneesGeographiques(recherchePointChronopostParCoordonneesGeographiques $parameters)
  {
      return $this->__soapCall('recherchePointChronopostParCoordonneesGeographiques', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }

  /**
   *
   *
   * @param rechercheDetailPointChronopostInter $parameters
   * @return rechercheDetailPointChronopostInterResponse
   */
  public function rechercheDetailPointChronopostInter(rechercheDetailPointChronopostInter $parameters)
  {
      return $this->__soapCall('rechercheDetailPointChronopostInter', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }

  /**
   *
   *
   * @param rechercheTourneeParTypeTourneeEtPosteComptable $parameters
   * @return rechercheTourneeParTypeTourneeEtPosteComptableResponse
   */
  public function rechercheTourneeParTypeTourneeEtPosteComptable(rechercheTourneeParTypeTourneeEtPosteComptable $parameters)
  {
      return $this->__soapCall('rechercheTourneeParTypeTourneeEtPosteComptable', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }

  /**
   *
   *
   * @param rechercheBtAvecPFParCodeproduitEtCodepostalEtDate $parameters
   * @return rechercheBtAvecPFParCodeproduitEtCodepostalEtDateResponse
   */
  public function rechercheBtAvecPFParCodeproduitEtCodepostalEtDate(rechercheBtAvecPFParCodeproduitEtCodepostalEtDate $parameters)
  {
      return $this->__soapCall('rechercheBtAvecPFParCodeproduitEtCodepostalEtDate', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }

  /**
   *
   *
   * @param rechercheBtParCodeproduitEtCodepostalEtDate $parameters
   * @return rechercheBtParCodeproduitEtCodepostalEtDateResponse
   */
  public function rechercheBtParCodeproduitEtCodepostalEtDate(rechercheBtParCodeproduitEtCodepostalEtDate $parameters)
  {
      return $this->__soapCall('rechercheBtParCodeproduitEtCodepostalEtDate', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }

  /**
   *
   *
   * @param recherchePointChronopost $parameters
   * @return recherchePointChronopostResponse
   */
  public function recherchePointChronopost(recherchePointChronopost $parameters)
  {
      return $this->__soapCall('recherchePointChronopost', array($parameters), array(
            'uri' => 'http://cxf.rechercheBt.soap.chronopost.fr/',
            'soapaction' => ''
          )
      );
  }
}
