//@ts-ignore
import classes from './Contact.module.css'

export function Contact(props: any) {
	return (
		<li className={classes.Contact}>
			<div
				className={classes.avatar}
				style={{ backgroundImage: `url(${props.avatar})` }}
			/>
			<div className={classes.text}>
				<p className={classes.nickname}>
					{props.nickname !== '' ? props.nickname : props.email}
				</p>
				{props.email !== '' ? (
					<p className={classes.email}>
						{props.nickname === '' ? '' : props.email}
					</p>
				) : (
					''
				)}
			</div>
		</li>
	)
}

export default Contact
