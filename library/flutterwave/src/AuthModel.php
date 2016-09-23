<?php
namespace Flutterwave;

/**
 * auth model for charging cards
 */
class AuthModel {
  /**
   * @var string bvn
   */
  const BVN = "BVN";

  /**
   * @var string pin
   */
  const PIN = "PIN";

  /**
   * @var string random_debit
   */
  const RANDOM_DEBIT = "RANDOM_DEBIT";

  /**
   * @var string vbvsecurecode
   */
  const VBVSECURECODE = "VBVSECURECODE";

  /**
   * @var string noauth
   */
  const NOAUTH = "NOAUTH";
}
