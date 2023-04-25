Name:         %{_name}
Summary:      Proxy server for routing external access
URL:          https://github.com/mjheick/httpd-php-revproxy
Vendor:       N/A
Group:        N/A
License:      Proprietary
Version:      %{_version}
Release:      %{_release}

#   list of sources
Source0:      %{name}-%{version}-%{release}.tar.gz

#   build information
BuildRoot:    %{_tmppath}/%{name}-%{version}-%{release}-root
BuildArch:    noarch

%description
Proxy server for routing external access

%prep
rm -rf %{name}-%{version}-%{release}
mkdir %{name}-%{version}-%{release}
tar -C %{name}-%{version}-%{release} -zxf %{SOURCE0}

%build

%install
rm -rf $RPM_BUILD_ROOT
cd %{name}-%{version}-%{release}

#   install program components
%%INSTALL_COMMANDS%%

%clean
rm -rf %{name}-%{version}-%{release}
rm -rf $RPM_BUILD_ROOT

%post
/usr/sbin/apachectl graceful

%postun
/usr/sbin/apachectl graceful

%files
%defattr(-, root, root)
/opt/httpd-php-revproxy/config
/usr/local/apache/htdocs/httpd-php-revproxy/
%config %attr(0644, apache, apache) /opt/httpd-php-revproxy/config/.htaccess
%config %attr(0644, apache, apache) /opt/httpd-php-revproxy/config/.htpasswd
%config %attr(0644, apache, apache) /opt/httpd-php-revproxy/config/redirects.yml
%config %attr(0644, apache, apache) /opt/httpd-php-revproxy/config/iplist.yml
%config %attr(0644, apache, apache) /opt/httpd-php-revproxy/config/creds.yml
%config /opt/httpd-php-revproxy/config/main.yml
