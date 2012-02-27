<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes" doctype-public="-//W3C/DTD HTML 4.01 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd" />
	<xsl:template match="/phynx/HTMLGUI">
		<html>
			<head>
				<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
				<meta name="revisit-after" content="14 days" />
				<meta http-equiv="content-encoding" content="gzip" />
				<meta http-equiv="cache-control" content="no-cache" />
				<title><xsl:value-of select="options/label[@for='title']" /></title>
				
				<link rel="shortcut icon" href="./images/FHSFavicon.ico" /> 
				
				<xsl:apply-templates select="stylesheets/css" />
				<xsl:apply-templates select="javascripts/js" />
				
				<script type="text/javascript">
					if(typeof contentManager == "undefined")
						alert("Die JavaScript-Dateien konnten nicht geladen werden.\nDies kann an der Server-Konfiguration liegen.\nBitte versuchen Sie, diese Anwendung in ein Unterverzeichnis zu installieren.");
				</script>

				<!--[if lt IE 7]>
				<script type="text/javascript">
					alert("Sie benötigen mindestens Internet Explorer Version 7!");
				</script>
				<![endif]-->
				
			</head>
			<body>
				<div id="DynamicJS" style="display: none;"></div>
				<div id="overlay" style="display: none;" class="backgroundColor0"></div>
				<div id="boxInOverlay" style="display: none;" class="backgroundColor0 borderColor1">
					<xsl:apply-templates select="overlay" />
				</div>
				<div id="container" style="display:none;">
					<div id="messenger" style="left:-210px;top:0px;" class="backgroundColor3 borderColor1"></div>
					<div id="navigation"></div>
					<xsl:if test="options/isDesktop/@value='true'">
						<div id="desktopWrapper">
							<div id="wrapperHandler" class="backgroundColor1 borderColor1"></div>
							<div id="wrapper">
								<table id="wrapperTable">
									<tr>
										<td id="wrapperTableTd1">
											<div id="contentLeft">
												<xsl:copy-of select="contentLeft" />
											</div>
										</td>
										<td id="wrapperTableTd2">
											<div id="contentRight">
												<xsl:copy-of select="contentRight" />
											</div>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</xsl:if>
					
					<xsl:if test="options/isDesktop/@value='false'">
						<div id="wrapper">
							<table id="wrapperTable">
								<tr>
									<td id="wrapperTableTd1">
										<div id="contentLeft">
											<xsl:copy-of select="contentLeft" />
										</div>
									</td>
									<td id="wrapperTableTd2">
										<div id="contentRight">
											<xsl:copy-of select="contentRight" />
										</div>
									</td>
								</tr>
							</table>
						</div>
					</xsl:if>
					
					<div id="windows"></div>
					<div id="footer">
						<p>
							<xsl:apply-templates select="footer" />
						</p>
					</div>
				</div>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="overlay">
		<form id="loginForm" onsubmit="return false;">
			<table class="loginWindow">
				<colgroup>
					<col class="backgroundColor2" style="width:120px;" />
					<col class="backgroundColor3" />
				</colgroup>
				<tr>
					<td class="backgroundColor2"><label><xsl:value-of select="options/label[@for='username']" />:</label></td>
					<td><input style="width:285px;" tabindex="1" onfocus="focusMe(this);" onblur="blurMe(this);" type="text" name="loginUsername" id="loginUsername" /></td>
				</tr>
				<tr>
					<td><label><xsl:value-of select="options/label[@for='password']" />:</label></td>
					<td>
						<img
							style="float:right;"
							class="mouseoverFade"
							onclick="if($('loginOptions').style.display=='none') $('loginOptions').style.display=''; else $('loginOptions').style.display='none';"
							src="./images/i2/settings.png">
							<xsl:attribute name="title">
								<xsl:value-of select="options/label[@for='optionsImage']" />
							</xsl:attribute>
						</img>
						<img
							style="float:right;margin-right:5px;"
							class="mouseoverFade"
							onclick="rmeP('Users', -1, 'lostPassword', [$('loginUsername').value], 'checkResponse(transport);');"
							src="./images/i2/hilfe.png">
							<xsl:attribute name="title">
								<xsl:value-of select="options/label[@for='lostPassword']" />
							</xsl:attribute>
						</img>
						<input
							style="width:240px;"
							onfocus="focusMe(this);"
							onblur="blurMe(this);"
							type="password"
							id="loginPassword"
							tabindex="2"
							onkeydown="if(event.keyCode == 13) userControl.doLogin();"
						/>
					</td>
				</tr>
				<tr id="loginOptions">
					<xsl:if test="count(../applications/*) &lt;= 1 or options/showApplicationsList/@value='0'">
						<xsl:attribute name="style">display:none;</xsl:attribute>
					</xsl:if>
					<td><label><xsl:value-of select="options/label[@for='application']" />:</label></td>
					<td>
						<select
							style="width:110px;float:right;"
							id="loginSprache"
							name="loginSprache"
							tabindex="4"
							onkeydown="if(event.keyCode == 13) userControl.doLogin();">
							<xsl:apply-templates select="./languages/lang" />
						</select>
						<xsl:if test="options/showApplicationsList/@value='1'">
							<select
								style="width:160px;margin-right:21px;"
								id="anwendung"
								name="anwendung"
								tabindex="3"
								onkeydown="if(event.keyCode == 13) userControl.doLogin();">
								<xsl:apply-templates select="../applications/app" />
							</select>
						</xsl:if>
						<xsl:if test="options/showApplicationsList/@value='0'">
							<input
								type="hidden"
								id="anwendung"
								name="anwendung">
								<xsl:attribute name="value"><xsl:value-of select="options/showApplicationsList/@defaultApplicationIfFalse" /></xsl:attribute>
							</input>
						</xsl:if>
						</td>
				</tr>
				<tr>
					<td colspan="2">
						<input
							class="backgroundColor3"
							type="button"
							style="float:right;width:30%;background-image:url(./images/i2/keys.png);"
							onclick="userControl.doLogin();">
							<xsl:attribute name="value"><xsl:value-of select="options/label[@for='login']" /></xsl:attribute>
						</input>
						<input
							type="checkbox"
							style="margin-right:5px;margin-top:4px;"
							name="saveLoginData"
							id="saveLoginData" />
						<label
							style="float:none;display:inline;font-weight:normal;"
							for="saveLoginData">
							<xsl:value-of select="options/label[@for='save']" />
						</label>
						<input type="hidden" value="" name="loginSHAPassword" id="loginSHAPassword" />
					</td>
				</tr>
				<xsl:if test="options/isDemo/@value='true'">
					<tr>
						<td colspan="2">
							<xsl:value-of select="options/label[@for='isDemo']" />
						</td>
					</tr>
				</xsl:if>
				<xsl:if test="options/isExtendedDemo/@value='true'">
					<tr>
						<td colspan="2">
							<xsl:value-of select="options/label[@for='extDemo']" />
						</td>
					</tr>
				</xsl:if>
			</table>
		</form>
	</xsl:template>
	
	<xsl:template match="css">
		<link rel="stylesheet" type="text/css"><xsl:attribute name="href"><xsl:value-of select="." /></xsl:attribute></link>
	</xsl:template>
	
	<xsl:template match="app">
		<option><xsl:attribute name="value"><xsl:value-of select="@value" /></xsl:attribute><xsl:value-of select="." /></option>
	</xsl:template>
	
	<xsl:template match="lang">
		<option><xsl:attribute name="value"><xsl:value-of select="@value" /></xsl:attribute><xsl:value-of select="." /></option>
	</xsl:template>
	
	<xsl:template match="js">
		<script type="text/javascript"><xsl:attribute name="src"><xsl:value-of select="." /></xsl:attribute></script>
	</xsl:template>
	
	<xsl:template match="menu">
		<xsl:apply-templates />
	</xsl:template>
	
	<xsl:template match="entry">
		<div>
			<img><xsl:attribute name="src"><xsl:value-of select="icon" /></xsl:attribute></img>
			<xsl:value-of select="label" />
		</div>
	</xsl:template>
	
	<xsl:template match="logo">
		<img><xsl:attribute name="src"><xsl:value-of select="." /></xsl:attribute></img>
	</xsl:template>
	
	<xsl:template match="footer">
			<img
				onclick="userControl.doLogout();"
				style="margin-left:15px;float:left;"
				class="mouseoverFade"
				title="Abmelden"
				alt="Abmelden"><xsl:attribute name="src"><xsl:value-of select="iconLogout" /></xsl:attribute></img>

			<xsl:if test="options/showLayoutButton/@value='1'">
				<img
					onclick="contextMenu.start(this, 'Colors','1','Einstellungen:','left', 'up');"
					style="float:right;margin-left:8px;margin-right:5px;"
					class="mouseoverFade"
					title="Layout"
					alt="Layout"><xsl:attribute name="src"><xsl:value-of select="iconLayout" /></xsl:attribute></img>
			</xsl:if>

			<xsl:if test="options/showHelpButton/@value='1'">
				<img
					onclick="window.open('http://www.phynx.de/support');"
					style="float:right;margin-left:8px;margin-right:5px;"
					class="mouseoverFade"
					title="Hilfe"
					alt="Hilfe"><xsl:attribute name="src"><xsl:value-of select="iconHelp" /></xsl:attribute></img>
			</xsl:if>

			<!--<xsl:if test="options/showDesktopButton/@value='true'">
				<img
					onclick="DesktopLink.toggle();"
					style="float:right;margin-left:8px;margin-right:5px;"
					class="mouseoverFade"
					title="Desktop"
					alt="Desktop"><xsl:attribute name="src"><xsl:value-of select="iconDesktop" /></xsl:attribute></img>
			</xsl:if>-->

			<xsl:if test="options/showCopyright/@value='1'">
				<xsl:copy-of select="copyright" />
			</xsl:if>
	</xsl:template>

</xsl:stylesheet>