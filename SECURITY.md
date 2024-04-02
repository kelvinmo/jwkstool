# Security Policy

This document sets out the security policy and procedures for the
jwkstool project.

  * [Supported versions](#supported-versions)
  * [How to report a security issue](#how-to-report-a-security-issue)
  * [Comments on this Policy](#comments-on-this-policy)

## Supported versions

jwkstool uses semantic versioning as its version numbering scheme.
The meanings of major version, minor version, patch version and initial
development follows the [Semantic Versioning Specification].

Security patches will be provided for the following:

- In the initial development phase (i.e. when the latest major version is
  version 0), only the latest patch version is supported
- Once major version 1 is released, patches will be provided to the two
  most recent major versions.


## How to report a security issue

If you discover a vulnerability in jwkstool, keep it confidential. 
*Do not disclose the vulnerability to anyone before the advisory is issued.*

jwkstool uses the [SimpleJWT] library for most of its functionality.
If you discover a vulunerability in that library, please report it
in accordance with the [Security Policy][simplejwt-security] of that
library. *Do not report it here.*

Provide details of the vulnerability at the [GitHub Security Advisories]
page.  For further information, please see the [GitHub documentation].

At a minimum, your report should include:

   1. the version of jwkstool, and your hosting environment
   2. the steps required to reproduce the problem
   3. any other information which you think would be useful in diagnosing
      the problem

If you know how to fix the problem or a temporary workaround, include it
in the report.

We will acknowledge your report as soon as we can.  We will use reasonable
endeavours to keep you informed while we investigate and create a fix.
We may ask you for additional information or guidance as part of our
investigation.

Some issue take time to correct and the process may involve a review of
the code for similar problems.

When a fix is ready, an advisory urging users to upgrade is published.
If the vulnerability is discovered for the first time, you will be credited in the advisory.

Report security bugs in third-party modules to the person or team maintaining
the module.

## Comments on this Policy

If you have suggestions on how this process could be improved please submit a
pull request.



[Semantic Versioning Specification]: https://semver.org/
[SimpleJWT]: https://github.com/kelvinmo/simplejwt/
[simplejwt-security]: https://github.com/kelvinmo/simplejwt/blob/master/SECURITY.md
[GitHub Security Advisories]: https://github.com/kelvinmo/jwkstool/security/advisories
[GitHub documentation]: https://docs.github.com/en/code-security/security-advisories/guidance-on-reporting-and-writing/privately-reporting-a-security-vulnerability
