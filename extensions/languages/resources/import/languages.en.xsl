<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
		xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		xmlns:exsl="http://exslt.org/common"
		extension-element-prefixes="exsl">


	<xsl:import href="parser.xsl"/>


	<xsl:output
			method="xml"
			encoding="utf-8"
			omit-xml-declaration="yes"
			indent="yes"
			/>


	<xsl:template match="/">
		<xsl:variable name="languages-ns">
			<xsl:apply-templates select="." mode="parseLanguages"/>
		</xsl:variable>

		<xsl:variable name="languages" select="exsl:node-set($languages-ns)/*"/>

		<languages>
			<xsl:for-each select="$languages">
				<xsl:copy>
					<xsl:for-each select="@*">
						<xsl:copy/>
					</xsl:for-each>

					<xsl:value-of select="english-name"/>
				</xsl:copy>
			</xsl:for-each>
		</languages>
	</xsl:template>


</xsl:stylesheet>
