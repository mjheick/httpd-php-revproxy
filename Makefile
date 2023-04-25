NAME=httpd-php-revproxy
VERSION=1.0
RELEASE=1

MANIFEST_FILES := $(shell find root -type f -o -type l)
rpm := ${NAME}-${VERSION}-${RELEASE}.rpm
tar := ${NAME}-${VERSION}-${RELEASE}.tar.gz

all: 

rpm: ${rpm}

${rpm}: ${tar} .$(NAME).spec
	rpmbuild --define='_tmppath /var/tmp/$(LOGNAME)' \
			 --define='_name ${NAME}' \
			 --define='_version ${VERSION}' \
			 --define='_release ${RELEASE}' \
			 --define='_builddir %{_tmppath}' \
			 --define='_sourcedir $(PWD)' \
			 --define='_rpmdir $(PWD)' \
			 --define='_rpmfilename ${rpm}' \
			 -bb .$(NAME).spec

.MANIFEST: Makefile ${MANIFEST_FILES}
	(cd root && find . -type f -printf "%P\n" -o -type l -printf "%P\n") > .MANIFEST
	
.$(NAME).spec: .MANIFEST ${MANIFEST_FILES} $(NAME).spec
	cat $(NAME).spec | perl -pe "if (\$$_ =~ /INSTALL_COMMANDS/) { \$$_ = qx(awk -- '{print \"mkdir -p \\$\\$$RPM_BUILD_ROOT/\\$\\$$(dirname \" \\$\\$$0 \") && cp -ar \" \\$\\$$0 \" \\$\\$$RPM_BUILD_ROOT/\" \\$\\$$0}' .MANIFEST);}" > .$(NAME).spec
	cat .MANIFEST | sed 's/^/\//' | sed 's/^\(.*\.\(yml\|tmpl\)\)$$/\%config(noreplace) \1/' >> .$(NAME).spec

${tar}: .MANIFEST $(MANIFEST_FILES)
	cd root && tar -zcf ../${tar} *

clean:
	rm -rf .$(NAME).spec *.tar.gz *.rpm .MANIFEST

deploy:

symlink:

install:

uninstallrpm:
	sudo yum -y remove $(NAME)

installrpm: rpm uninstallrpm
	sudo rpm -i ${rpm}
	sudo /etc/init.d/httpd graceful
