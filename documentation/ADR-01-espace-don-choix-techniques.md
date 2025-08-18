## ADR - O1 - Technical Choice for the Donation Space

### Why a Plugin and Not a Child Theme?

The donation space is designed as a WordPress plugin rather than a child theme. Initially, the child theme seemed like the ideal solution. However, during development, we encountered several obstacles that led us to opt for a plugin.

#### Issues with the Child Theme

1. **Incompatibility with Amnesty Branding Theme**: The Amnesty branding theme, which includes Amnesty-specific UI elements, was not fully compatible with a child theme. For example, it was not possible to use the "Amnesty yellow button".

2. **Specific Needs**: We had specific technical needs that the child theme could not meet optimally.

### The Needs

From a technical standpoint, our needs were as follows:

1. **Predictive URLs**: The URLs used in the donation space needed to be predictive, meaning we could determine in advance what the URLs of the different pages would be to create links between them.

2. **Limit Manual Actions**: All pages of the donation space needed to be easily deployed across different environments with minimal manual actions.

3. **Proximity to the Humanity Theme**: Stay as close as possible to the Humanity Theme with minimal modifications.

4. **Integration and Autonomy**: The donation space needed to integrate seamlessly into the global site structure but also function autonomously during the site's construction.

### Addressing the Needs

#### 1. Predictive URLs

To meet this need, we configured the plugin to recreate the donation space hierarchy with each activation. This ensures that the necessary pages for the donation space are always present.

#### 2. Limit Manual Actions

With a child theme, it would have been necessary to create page templates and manually link them via the admin interface. The plugin approach automatically creates the hierarchy by linking each page to its template, thus avoiding repetitive manual actions across different environments.

#### 3. Proximity to the Humanity Theme


Initially, we wanted to stay as close as possible to the Humanity Theme. However, we encountered several issues:

- **Incomplete Documentation**: The theme's documentation was insufficient for developers. For example, there were no instructions on creating certain UI elements.
- **Different Graphic Identity**: Amnesty France wanted a slightly different graphic identity from Amnesty International.
- **Designer Dissatisfaction**: Our designers were not satisfied with the appearance of some pages built solely with the theme's CSS.

For these reasons, we introduced custom CSS for the donation space, contained in the `assets/css/styles.css` file.

#### Some Principles

- **Class Prefixes**: Each class is prefixed with `aif` to avoid collisions.
- **Utility Approach**: Initially, we adopted an approach similar to [Tailwind CSS](https://tailwindcss.com/) by creating utility classes (`.aif-mt1w` to add `margin-top`, etc.).
- **BEM**: For some UI elements, we used the ["Block Element Modifier" (BEM)](https://getbem.com/) naming convention.

### Integration into the Global Site and "Standalone" Version

To meet this need, we decided that each URL of the donation space would be a sub-route prefixed by `/espace-don`.

