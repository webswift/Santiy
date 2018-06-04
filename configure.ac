AC_PREREQ([2.69])
AC_CONFIG_MACRO_DIR([./aclocal])
AC_INIT([sanityos], [0.0.1-dev], [])

AM_INIT_AUTOMAKE([1.13 -Wall -Werror std-options subdir-objects tar-pax])
PKG_PROG_PKG_CONFIG

AC_PROG_INSTALL
#AC_PROG_LIBTOOL
AC_PROG_MAKE_SET
AC_PROG_MKDIR_P

#check php
AC_PATH_PROG([PHP], [php])
AM_CONDITIONAL(HAVE_PHP, [test "x$PHP" != "x"])
AM_COND_IF([HAVE_PHP],,[AC_MSG_ERROR([required program 'php' not found.])])


AC_ARG_ENABLE([tests],
			  AS_HELP_STRING([--enable-tests], [build tests [default=yes]]),
			  [], enable_tests=yes
			  )
have_tests=yes
if test "$enable_tests" = "no"; then
	have_tests="no"
fi
AM_CONDITIONAL(WITH_TESTS, [test "$have_tests" = "yes"])
	
#check php unit
#AC_PATH_PROG([PHPUNIT], [phpunit])
#AM_CONDITIONAL(HAVE_PHPUNIT, [test "x$PHPUNIT" != "x"])
	
#node npm
AC_PATH_PROG([NPM], [npm])
AM_CONDITIONAL(HAVE_NPM, [test "x$NPM" != "x"])
	
#ruby gem
#AC_PATH_PROG([GEM], [gem])
#AM_CONDITIONAL(HAVE_GEM, [test "x$GEM" != "x"])

#curl
AC_PATH_PROG([CURL], [curl])
AM_CONDITIONAL(HAVE_CURL, [test "x$CURL" != "x"])

#git
AC_PATH_PROG([GIT], [git])
AM_CONDITIONAL(HAVE_GIT, [test "x$GIT" != "x"])

#phpunit is need for tests
if test "$have_tests" = "yes" ; then
	#check php unit
	#AM_COND_IF([HAVE_PHPUNIT],,[AC_MSG_ERROR([required program 'phpunit' not found.])])
	
	#node npm
	AM_COND_IF([HAVE_NPM],,[AC_MSG_ERROR([required program 'npm' not found.])])
	
	#ruby gem
	#AM_COND_IF([HAVE_GEM],,[AC_MSG_ERROR([required program 'gem' not found.])])
	
	#curl
	AM_COND_IF([HAVE_CURL],,[AC_MSG_ERROR([required program 'curl' not found.])])

fi

AM_EXTRA_RECURSIVE_TARGETS([
							download-dependencies-production
							download-dependencies 
							install-dependencies-production
							install-dependencies 
							update-downloaded-dependencies
							update-downloaded-dependencies-production
							check-acceptance
							check-functional
							run-mailcatcher
							stop-mailcatcher
							merge-starterkit
							production-optimize
							])
AC_CONFIG_FILES([
				 Makefile
				 ])

AC_OUTPUT


# Print results.
AC_MSG_RESULT([])
AC_MSG_RESULT([ $PACKAGE version $VERSION configured successfully.])
AC_MSG_RESULT([])

