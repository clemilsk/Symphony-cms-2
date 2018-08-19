<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
		xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		xmlns:exsl="http://exslt.org/common"
		extension-element-prefixes="exsl">



	<xsl:template match="/" mode="parseLanguages">
		<xsl:variable name="coded">
			<xsl:for-each select="table/tbody/tr">
				<lang>
					<xsl:attribute name="code">
						<xsl:apply-templates select="." mode="langISO-639-1"/>
					</xsl:attribute>

					<xsl:copy-of select="*"/>
				</lang>
			</xsl:for-each>
		</xsl:variable>

		<xsl:apply-templates select="exsl:node-set($coded)/*" mode="lang"/>
	</xsl:template>


	<xsl:template match="*" mode="lang">
		<xsl:copy>
			<xsl:for-each select="@*">
				<xsl:copy/>
			</xsl:for-each>

			<native-name>
				<xsl:apply-templates select="." mode="langNativeName"/>
			</native-name>
			<english-name>
				<xsl:apply-templates select="." mode="langEnglishName"/>
			</english-name>
			<dir>
				<xsl:apply-templates select="." mode="langTextDirection"/>
			</dir>
			<iso-639-1>
				<xsl:apply-templates select="." mode="langISO-639-1"/>
			</iso-639-1>
			<iso-639-2t>
				<xsl:apply-templates select="." mode="langISO-639-2T"/>
			</iso-639-2t>
			<iso-639-2b>
				<xsl:apply-templates select="." mode="langISO-639-2B"/>
			</iso-639-2b>
			<iso-639-3>
				<xsl:apply-templates select="." mode="langISO-639-3"/>
			</iso-639-3>
			<iso-639-6>
				<xsl:apply-templates select="." mode="langISO-639-6"/>
			</iso-639-6>
		</xsl:copy>
	</xsl:template>

	<!-- Get native name -->
	<xsl:template match="*" mode="langNativeName">
		<xsl:value-of select="td[4]"/>
	</xsl:template>

	<!-- Get English name -->
	<xsl:template match="*" mode="langEnglishName">
		<xsl:value-of select="td[3]"/>
	</xsl:template>

	<!-- Get text direction -->
	<xsl:template match="*" mode="langTextDirection">
		<xsl:choose>
			<xsl:when test="td[4]/@dir">
				<xsl:value-of select="td[4]/@dir"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>ltr</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Get ISO 639-1 code -->
	<xsl:template match="*" mode="langISO-639-1">
		<xsl:value-of select="td[5]"/>
	</xsl:template>

	<!-- Get ISO 639-2/T code -->
	<xsl:template match="*" mode="langISO-639-2T">
		<xsl:value-of select="td[6]"/>
	</xsl:template>

	<!-- Get ISO 639-2/B code -->
	<xsl:template match="*" mode="langISO-639-2B">
		<xsl:value-of select="td[7]"/>
	</xsl:template>

	<!-- Get ISO 639-3 code -->
	<xsl:template match="*" mode="langISO-639-3">
		<xsl:value-of select="td[8]"/>
	</xsl:template>

	<!-- Get ISO 639-6 code -->
	<xsl:template match="*" mode="langISO-639-6">
		<xsl:value-of select="td[9]"/>
	</xsl:template>




	<!-- Exclude Interlingua | Interlingue -->
	<xsl:template match="lang[ @code = 'ia' ] | lang[ @code = 'ie' ]" mode="lang"/>



</xsl:stylesheet>
